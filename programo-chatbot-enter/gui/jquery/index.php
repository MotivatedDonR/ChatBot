<?php
/***************************************
 * http://www.program-o.com
 * PROGRAM O
 * Version: 2.6.11
 * FILE: index.php
 * AUTHOR: Elizabeth Perreau and Dave Morton
 * DATE: FEB 01 2016
 * DETAILS: This is the interface for the Program O JSON API
 ***************************************/

$cookie_name = 'Program_O_JSON_GUI';
$botId = filter_input(INPUT_GET, 'bot_id');
$convoId = filter_input(INPUT_GET, 'convo_id');
$convo_id = (isset($_COOKIE[$cookie_name])) ? $_COOKIE[$cookie_name] : ($convoId !== false && $convoId !== null) ? $convoId : jq_get_convo_id();
if (empty($convo_id)) $convo_id = jq_get_convo_id();
$bot_id = (isset($_COOKIE['bot_id'])) ? $_COOKIE['bot_id'] : ($botId !== false && $botId !== null) ? $botId : 1;

if (is_nan($bot_id) || empty($bot_id))
{
    $bot_id = 1;
}

setcookie('bot_id', $bot_id);

// Experimental code
$HXFP  = (isset($_SERVER['HTTP_X_FORWARDED_PORT'])) ? $_SERVER['HTTP_X_FORWARDED_PORT'] : '';
$HSP   = (isset($_SERVER['SERVER_PORT'])) ? $_SERVER['SERVER_PORT'] : '';
$HTTPS = (isset($_SERVER['HTTPS'])) ? $_SERVER['HTTPS'] : '';
$HHOST = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '';
$protocol = ((!empty($HTTPS) && $HTTPS != 'off') || $HSP == 443 || $HXFP == 443) ? "https://" : "http://";
$base_URL = $protocol . $HHOST;                                   // set domain name for the script
$this_path = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__)));  // The current location of this file, normalized to use forward slashes
$this_path = str_replace($_SERVER['DOCUMENT_ROOT'], $base_URL, $this_path);       // transform it from a file path to a URL
$url = str_replace('gui/jquery', 'chatbot/conversation_start.php', $this_path);   // and set it to the correct script location
/*
  Example URL's for use with the chatbot API
  $url = 'http://api.program-o.com/v2.3.1/chatbot/';
  $url = 'http://localhost/Program-O/Program-O/chatbot/conversation_start.php';
  $url = 'chat.php';
*/

$display = "The URL for the API is currently set as:<br />\n$url.<br />\n";
$display .= 'Test this to make sure it is correct by <a href="' . $url . '?say=hello">clicking here</a>. Then remove this message from gui/jquery/index.php' . PHP_EOL;
$display .= 'And don\'t forget to upload your AIML files in the admin area otherwise you will not get a response!' . PHP_EOL;
#$display = '';

/**
 * Function jq_get_convo_id
 *
 *
 * @return string
 */
function jq_get_convo_id()
{
    global $cookie_name;

    session_name($cookie_name);
    session_start();
    $convo_id = session_id();
    session_destroy();
    setcookie($cookie_name, $convo_id);

    return $convo_id;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="Description" content="A Free Open Source AIML PHP MySQL Chatbot called Program-O. Version2"/>
    <meta name="keywords" content="Open Source, AIML, PHP, MySQL, Chatbot, Program-O, Version2"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program O jQuery GUI Examples</title>
    <link rel="stylesheet" type="text/css" href="main.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="chat.css" media="all"/>
    <link rel="icon" href="./favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon"/>
    <link rel="icon" href="./chat_start_button.png" type="image/png"/>
    <link rel="stylesheet" href="//cdn.materialdesignicons.com/3.6.95/css/materialdesignicons.min.css">
    <script type='text/javascript' src='./moment.js'></script>
    <script type="text/javascript">
    var URL = '<?php echo $url ?>';
    </script>
</head>
<body>
<h3>Program O JSON GUI</h3>
<p class="center">
    This is a simple example of how to access the Program O chatbot using the JSON API. Feel free to change the HTML
    code for this page to suit your specific needs. For more advanced uses, please visit us at <a
            href="http://www.program-o.com/">
        Program O</a>.
</p>

<!-- 
    ////////////////// chat bot ui start ///////////////////
-->

<a class='chat-start-button' id='chatStartButton'>
    <img src='chat-start-button.png'/>
</a>
<div class='chat-bot'>
    <div class='bot-header'>
        <div class='bot-img-status'>
            <div class='online-lamp'>        
            </div>
        </div>
        <div class='bot-text-status'>
            <h2 class='chat-title'>Chat Bot</h2>
            <h3 class='text-status'>Online</h3>
        </div>
        <div class='social-link'>
                <a href='http://facebook.com'><span class='go-facebook mdi mdi-facebook'></span></a>
                <a href='https://twitter.com'><span class='go-twitter mdi mdi-twitter'></span></a>
        </div>

        <span class='bot-close mdi mdi-close'></span>
    </div>
    <div class='chat-history' id='chatHistoryDiv'>
        <ul id='chatHistory'>
        </ul>
    </div>
    <div class='message-box'>
        <input type="hidden" name="convo_id" id="convo_id" value="<?php echo $convo_id; ?>"/>
        <input type="hidden" name="bot_id" id="bot_id" value="<?php echo $bot_id; ?>"/>
        <input type="hidden" name="format" id="format" value="json"/>
        <textarea placeholder='Type your message here' id='myStory' onkeypress="onTestChange()"></textarea>
        <a id='storySubmit'>
            <span class='mdi mdi-send'></span>
        </a>
    </div>
</div>

<!-- 
    ////////////////// chat bot ui start ///////////////////
-->

<!--<div id="urlwarning"><?php echo $display ?></div> -->
<script type="text/javascript" src="jquery-1.9.1.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // put all your jQuery goodness in here.
        $('#chatStartButton').click(function(){
            $('.chat-bot').css('display', 'block');
            chatSubmit('started chatting');
            $(this).css('display', 'none');            
        });

        $('.bot-close').click(function(){
            $('.chat-bot').css('display', 'none');
            $('#chatStartButton').css('display','block');            
        });

        $('#storySubmit').click(function (){
            var story = '';
            story = $('#myStory').val();
            if(story == '') return;
            chatSubmit(story);
        });
    });

    function onTestChange() {
        var key = window.event.keyCode;

        // If the user has pressed enter
        if (key === 13) {
            var story = document.getElementById("myStory").value;
            document.getElementById("myStory").value = '';
            chatSubmit(story);
        }
        else {
            return true;
        }
    }
    function chatSubmit(story) {
        var bot_id = $('#bot_id').val();
        var convo_id = $('#convo_id').val();

        const myStory = {
            bot_id,
            convo_id,
            'format': 'json',
            'say': story
        }
        console.log(myStory);
        $.get(URL, myStory, function (data) {
            console.info('Data =', data);
            var b = data.botsay;
            if (b.indexOf('[img]') >= 0) {
                b = showImg(b);
            }
            if (b.indexOf('[link') >= 0) {
                b = makeLink(b);
            }
            var usersay = data.usersay;
            if (usersay != 'started chatting') {
                $('#chatHistory').append("<li><h3 class='submit-time'>"+moment().calendar()+"</h3><div class='user-story'>"+usersay+"</div></li>");
            }

            if (b.indexOf('<li>') >= 0) {
                $('#chatHistory').append(b);
                var time = moment().calendar();
                var pre = "<h3 class='submit-time'>"+time+"</h3><img class='saying' src='./wave.png'/>"
                $('.chat-history ul li:last-child div:first-child').before(pre);
            }
            else {
                $('#chatHistory').append("<li><h3 class='submit-time'>"+moment().calendar()+"</h3><img class='saying' src='./wave.png'/><div class='bot-story'><img class='emoji' src='./svg/smiling.svg'/>"+b+"</div></li>");
            }
            var objDiv = document.getElementById('chatHistoryDiv');
            objDiv.scrollTop = objDiv.scrollHeight;
            console.log(objDiv);
            console.log(objDiv.scrollHeight);
            $('#chatHistory li:last-child').focus();

        }, 'json').fail(function (xhr, textStatus, errorThrown) {
            console.error('XHR =', xhr.responseText);
        });
    }
    function showImg(input) {
        var regEx = /\[img\](.*?)\[\/img\]/;
        var repl = '<br><a href="$1" target="_blank"><img src="$1" alt="$1" width="150" /></a>';
        var out = input.replace(regEx, repl);
        console.log('out = ' + out);
        return out
    }
    function makeLink(input) {
        var regEx = /\[link=(.*?)\](.*?)\[\/link\]/;
        var repl = '<a href="$1" target="_blank">$2</a>';
        var out = input.replace(regEx, repl);
        console.log('out = ' + out);
        return out;
    }
</script>
</body>
</html>