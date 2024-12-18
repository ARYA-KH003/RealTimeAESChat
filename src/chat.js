const senderId = document.querySelector('meta[name="sender_id"]').content;
const receiverId = document.querySelector('meta[name="receiver_id"]').content;

const lowId = Math.min(senderId, receiverId);
const highId = Math.max(senderId, receiverId);
const keyInput = (lowId * highId) + (lowId + highId);
const userPair = `${lowId}:${highId}`;

const SHARED_AES_KEY = CryptoJS.PBKDF2(keyInput.toString(), userPair, {
    keySize: 256 / 32,
    iterations: 1000,
});

console.log("Generated Shared AES Key:", SHARED_AES_KEY.toString());

function encryptMessage(message) {
    const iv = CryptoJS.lib.WordArray.random(16);
    const encrypted = CryptoJS.AES.encrypt(message, SHARED_AES_KEY, { iv: iv });
    return { ciphertext: encrypted.toString(), iv: iv.toString() };
}


function decryptMessage(encryptedMessage, iv) {
    const decrypted = CryptoJS.AES.decrypt(encryptedMessage, SHARED_AES_KEY, {
        iv: CryptoJS.enc.Hex.parse(iv),
    });
    return decrypted.toString(CryptoJS.enc.Utf8);
}


async function fetchMessages() {
    const response = await fetch(`fetch_messages.php?receiver_id=${receiverId}`);
    const data = await response.json();

    const chatBox = document.getElementById("chat-box");
    const previousHeight = chatBox.scrollHeight;

    chatBox.innerHTML = "";

    data.forEach((msg) => {
        const decryptedMessage = decryptMessage(msg.encrypted_message, msg.iv);
        const messageClass = msg.sender_id == senderId ? "message-sent" : "message-received";
        chatBox.innerHTML += `<div class="message ${messageClass}">${decryptedMessage}</div>`;
    });
    if (chatBox.scrollHeight > previousHeight) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}


async function sendMessage() {
    const messageInput = document.getElementById("message");
    const message = messageInput.value.trim();
    if (!message) return;

    const { ciphertext, iv } = encryptMessage(message);

    await fetch("send_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            sender_id: senderId,
            receiver_id: receiverId,
            message: ciphertext,
            iv: iv,
        }),
    });

    messageInput.value = "";
    fetchMessages();
}

setInterval(fetchMessages, 2000);

document.getElementById("message").addEventListener("input", function () {
    document.querySelector("button[type='submit']").disabled = !this.value.trim();
});
