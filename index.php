<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chatroom</title>
    <!-- Skeleton CSS Framework -->
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/skeleton.css">
	<link rel="stylesheet" href="css/custom.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/favicon.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <div id="chat-container"></div>
        <div class="input-area">
            <div class="input-container">
                <textarea id="message-input" placeholder="Type your message..."></textarea>
                <label for="attachment-input" id="attachment-label">
                    <img src="icons/attachment.svg" alt="Attach files" width="16" height="16">
                </label>
                <input type="file" id="attachment-input" multiple>
                <button id="send-button" class="button button-primary">Send</button>
            </div>
            <div id="attachment-preview"></div>
            <div id="status-message"></div>
        </div>
    </div>
    <div class="edit-popup">
        <textarea id="edit-message-input"></textarea>
        <div class="edit-popup-buttons">
            <button id="cancel-edit" class="button">Cancel</button>
            <button id="save-edit" class="button button-primary">Save</button>
        </div>
    </div>
    <div class="overlay"></div>

    <script>
        $(document).ready(function() {
            let attachments = [];
            let expandedMessages = new Set();
            let currentEditId = null;

            function loadMessages() {
                $.ajax({
                    url: 'get_messages.php',
                    type: 'GET',
                    dataType: 'html',
                    success: function(data) {
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
                        // Scroll to bottom after loading messages
                        $('#chat-container').scrollTop($('#chat-container')[0].scrollHeight);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#status-message').text('Error loading messages: ' + textStatus);
                    }
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

            function sendMessage() {
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
            }

            $('#send-button').click(function() {
                sendMessage();
            });

            // Handle Enter key press to send message and Shift+Enter for new line
            $('#message-input').keydown(function(e) {
                if (e.keyCode === 13) { // Enter key
                    if (!e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                }
            });

            $(document).on('click', '.edit-button', function() {
                var messageId = $(this).data('id');
                var messageElement = $(this).closest('.message').find('.message-text');
                var tempDiv = $('<div>').html(messageElement.html());
                tempDiv.find('br').replaceWith('\n');
                var currentMessage = tempDiv.text();
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

            // Initial load of messages
            loadMessages();
            
            // Refresh messages every 15 seconds
            setInterval(loadMessages, 15000);
            
            // Adjust height for chat container
            function adjustHeight() {
                const windowHeight = $(window).height();
                const inputAreaHeight = $('.input-area').outerHeight();
                $('#chat-container').css('height', windowHeight - inputAreaHeight - 20);
            }
            
            $(window).resize(adjustHeight);
            adjustHeight();
        });
    </script>
</body>
</html>
