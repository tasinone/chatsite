<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chatroom</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            background-color: #ff0033;
            color: white;
            padding: 1rem 0;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        nav ul {
            list-style-type: none;
            display: flex;
        }
        nav ul li {
            margin-left: 1rem;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
        }
        main {
            padding: 2rem 0;
        }
        #chat-container {
            border: 1px solid #ccc;
            padding: 10px;
            height: 600px;
            overflow-y: auto;
            margin-bottom: 10px;
        }
        .message {
            display: flex;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .message-content {
            flex-grow: 1;
            margin-right: 10px;
        }
        .message-text {
            white-space: pre-wrap;
            word-break: break-word;
        }
        .message-text.truncated {
            max-height: 4.8em;
            overflow: hidden;
        }
        .see-more {
            color: #ff0033;
            cursor: pointer;
        }
        .message-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 60px;
        }
        .message-actions button {
            background-color: #ff0033;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
            margin-bottom: 5px;
        }
        .message-actions button:hover {
            background-color: #cc0029;
        }
        .input-container {
            display: flex;
            margin-bottom: 10px;
        }
        #message-input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px 0 0 3px;
            resize: vertical;
            min-height: 38px;
        }
        #attachment-input {
            display: none;
        }
        #attachment-label {
            background-color: #f0f0f0;
            padding: 10px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-left: none;
        }
        #send-button {
            background-color: #ff0033;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 0 3px 3px 0;
        }
        #send-button:hover {
            background-color: #cc0029;
        }
        #status-message {
            color: red;
            margin-top: 10px;
        }
        .attachment-preview {
            margin-top: 5px;
            font-size: 0.9em;
            color: #666;
        }
        .attachments a {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .message-text {
            white-space: pre-wrap;
            word-break: break-word;
        }
        .message-text.truncated {
            max-height: 4.8em;
            overflow: hidden;
        }
        .see-more {
            color: #ff0033;
            cursor: pointer;
        }

         .message {
            display: flex;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .message-content {
            flex-grow: 1;
            margin-right: 10px;
        }
        .message-text {
            white-space: pre-wrap;
            word-break: break-word;
        }
        .message-text.truncated {
            max-height: 4.8em;
            overflow: hidden;
        }
        .see-more {
            color: #ff0033;
            cursor: pointer;
        }
        .message-actions {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            width: 60px;
        }
        .message-actions button {
            background-color: #ff0033;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
            margin-bottom: 5px;
        }
        .message-actions button:hover {
            background-color: #cc0029;
        }
        .edit-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .edit-popup textarea {
            width: 100%;
            min-height: 100px;
            margin-bottom: 10px;
            padding: 5px;
        }

        .edit-popup-buttons {
            text-align: right;
        }

        .edit-popup-buttons button {
            margin-left: 10px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }
        @media (max-width: 600px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            nav ul {
                margin-top: 1rem;
            }
            nav ul li:first-child {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">Chatroom</div>
                <nav>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main class="container">
        <div id="chat-container"></div>
        <div class="input-container">
            <textarea id="message-input" placeholder="Type your message..."></textarea>
            <label for="attachment-input" id="attachment-label">📎</label>
            <input type="file" id="attachment-input" multiple>
            <button id="send-button">Send</button>
        </div>
        <div id="attachment-preview"></div>
        <div id="status-message"></div>
    </main>
    <div class="edit-popup">
        <textarea id="edit-message-input"></textarea>
        <div class="edit-popup-buttons">
            <button id="cancel-edit">Cancel</button>
            <button id="save-edit">Save</button>
        </div>
    </div>
    <div class="overlay"></div>

    <script>
        $(document).ready(function() {
    let attachments = [];
    let expandedMessages = new Set();
    let currentEditId = null;

    function loadMessages() {
        $.get('get_messages.php', function(data) {
            $('#chat-container').html(data);
            $('.message-text').each(function() {
                let messageId = $(this).closest('.message').data('id');
                if ($(this).height() > 72) { // 4.8em * 16px (assuming 1em = 16px)
                    if (expandedMessages.has(messageId)) {
                        $(this).removeClass('truncated');
                        if (!$(this).next().hasClass('see-more')) {
                            $(this).after('<span class="see-more">See less</span>');
                        } else {
                            $(this).next('.see-more').text('See less');
                        }
                    } else {
                        $(this).addClass('truncated');
                        if (!$(this).next().hasClass('see-more')) {
                            $(this).after('<span class="see-more">See more</span>');
                        } else {
                            $(this).next('.see-more').text('See more');
                        }
                    }
                } else {
                    $(this).removeClass('truncated');
                    $(this).next('.see-more').remove();
                }
            });
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#status-message').text('Error loading messages: ' + textStatus);
        });
    }

    $('#attachment-input').change(function(e) {
        attachments = Array.from(e.target.files);
        updateAttachmentPreview();
    });

    function updateAttachmentPreview() {
        let preview = attachments.map(file => {
            let name = file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name;
            return name;
        }).join(', ');
        $('#attachment-preview').text(preview ? 'Attachments: ' + preview : '');
    }

    $('#send-button').click(function() {
        var message = $('#message-input').val();
        if (message || attachments.length > 0) {
            var formData = new FormData();
            formData.append('message', message);
            attachments.forEach((file, index) => {
                formData.append('attachment[]', file);
            });

            $.ajax({
                url: 'send_message.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#message-input').val('');
                        attachments = [];
                        updateAttachmentPreview();
                        loadMessages();
                        $('#status-message').text('');
                    } else {
                        $('#status-message').text('Error: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#status-message').text('Error sending message: ' + textStatus);
                }
            });
        }
    });

    $(document).on('click', '.edit-button', function() {
        var messageId = $(this).data('id');
        var currentMessage = $(this).closest('.message').find('.message-text').html();
        currentMessage = currentMessage.replace(/<br>/g, '\n');
        $('#edit-message-input').val(currentMessage);
        $('.edit-popup, .overlay').show();
        currentEditId = messageId;
    });

    $('#cancel-edit').click(function() {
        $('.edit-popup, .overlay').hide();
        currentEditId = null;
    });

    $('#save-edit').click(function() {
        var newMessage = $('#edit-message-input').val();
        if (newMessage !== null && currentEditId !== null) {
            $.ajax({
                url: 'edit_message.php',
                type: 'POST',
                data: JSON.stringify({id: currentEditId, message: newMessage}),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        loadMessages();
                        $('.edit-popup, .overlay').hide();
                        currentEditId = null;
                        $('#status-message').text('');
                    } else {
                        $('#status-message').text('Error: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#status-message').text('Error editing message: ' + textStatus);
                }
            });
        }
    });

    $(document).on('click', '.delete-button', function() {
        var messageId = $(this).data('id');
        if (confirm('Are you sure you want to delete this message?')) {
            $.ajax({
                url: 'delete_message.php',
                type: 'POST',
                data: JSON.stringify({id: messageId}),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        loadMessages();
                        $('#status-message').text('');
                    } else {
                        $('#status-message').text('Error: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#status-message').text('Error deleting message: ' + textStatus);
                }
            });
        }
    });

    $(document).on('click', '.see-more', function() {
        let messageText = $(this).prev('.message-text');
        let messageId = $(this).closest('.message').data('id');
        messageText.toggleClass('truncated');
        if (messageText.hasClass('truncated')) {
            $(this).text('See more');
            expandedMessages.delete(messageId);
        } else {
            $(this).text('See less');
            expandedMessages.add(messageId);
        }
    });

    loadMessages();
    setInterval(loadMessages, 5000000);
});
    </script>
</body>
</html>