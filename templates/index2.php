<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chatroom</title>
    <!-- Skeleton CSS Framework -->
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/skeleton.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/favicon.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<style>
	/* Minimal custom styling to supplement Skeleton */
	html, body {
		height: 100%;
		margin: 0;
	}
	body {
		display: flex;
		flex-direction: column;
	}
	.container {
		width: 100%;
		max-width: 850px;
		margin: 0 auto;
		padding: 0 3px;
		flex: 1;
		display: flex;
		flex-direction: column;
	}
	#chat-container {
		border: 1px solid #E1E1E1;
		border-radius: 4px;
		padding: 10px;
		flex: 1;
		overflow-y: auto;
		margin-bottom: 10px;
		margin-top: 10px;
	}
	.message {
		display: flex;
		margin-bottom: 10px;
		padding: 10px;
		background-color: #F5F5F5;
		border-radius: 4px;
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
		color: #1EAEDB;
		cursor: pointer;
	}
	.message-actions {
		display: flex;
		align-items: flex-start;
	}
	.message-actions button {
		background: none;
		border: none;
		padding: 5px;
		cursor: pointer;
		margin-left: 5px;
	}
	.message-actions button img {
		width: 16px;
		height: 16px;
	}
	.input-area {
		position: sticky;
		bottom: 0px;
		background-color: #fff;
		padding-bottom: 10px;
	}
	.input-container {
		display: flex;
		margin-bottom: 10px;
	}
	#message-input {
		flex-grow: 1;
		height: 38px;
		padding: 6px 10px;
		border: 1px solid #D1D1D1;
		border-radius: 4px 0 0 4px;
		resize: vertical;
		min-height: 38px;
	}
	#attachment-input {
		display: none;
	}
	#attachment-label {
		background-color: #F1F1F1;
		padding: 0 10px;
		cursor: pointer;
		border: 1px solid #D1D1D1;
		border-left: none;
		display: flex;
		align-items: center;
	}
	#send-button {
		border-radius: 0 4px 4px 0;
		margin: 0;
	}
	#status-message {
		color: #D9534F;
		margin-top: 10px;
	}
	.attachment-preview {
		margin-top: 5px;
		font-size: 0.9em;
		color: #777;
	}
	.attachments a {
		display: inline-block;
		max-width: 100%;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	.edit-popup {
		display: none;
		position: fixed;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		background-color: white;
		padding: 20px;
		border-radius: 4px;
		box-shadow: 0 2px 10px rgba(0,0,0,0.2);
		z-index: 1000;
		width: 90%;
		max-width: 500px;
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
</style>
<body>
    <div class="container">
        <div id="chat-container"></div>
        <div class="input-area">
            <div class="input-container">
                <textarea id="message-input" placeholder="Type your message..."></textarea>
                <label for="attachment-input" id="attachment-label">📎</label>
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