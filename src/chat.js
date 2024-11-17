document.addEventListener('DOMContentLoaded', function () {
    var chatBox = document.getElementById('chat-messages');
    var chatUser = new URLSearchParams(window.location.search).get('user');
    
    function fetchMessages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_messages.php?user=' + chatUser, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                chatBox.innerHTML = xhr.responseText;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        };
        xhr.send();
    }

    // Send message via AJAX
    var sendMessageForm = document.getElementById('send-message');
    var sendButton = sendMessageForm.querySelector('button[type="submit"]');

    sendMessageForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var messageInput = sendMessageForm.querySelector('input[name="message"]');
        var formData = new FormData(sendMessageForm);
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'send_message.php', true);
        
        sendButton.disabled = true;

        xhr.onload = function () {
            if (xhr.status === 200) {
                messageInput.value = '';
                sendButton.disabled = false;
                fetchMessages();
            } else {
                sendButton.disabled = false;
                chatBox.innerHTML += '<p class="error">Message failed to send. Please try again.</p>';
            }
        };
        xhr.send(formData);
    });

    fetchMessages();
    setInterval(fetchMessages, 5000);    
});
