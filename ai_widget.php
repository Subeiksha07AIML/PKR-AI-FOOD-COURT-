<!-- Global Floating AI Assistant Widget -->
<div id="ai-assistant-wrapper" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; font-family: sans-serif;">
    <button id="ai-toggle-btn" onclick="toggleAIChat()" style="background: #ff4757; color: white; border: none; border-radius: 50%; width: 55px; height: 55px; font-size: 24px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: center;">🤖</button>
    
    <div id="ai-chat-box" style="display: none; width: 320px; height: 420px; background: white; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); flex-direction: column; overflow: hidden; position: absolute; bottom: 70px; right: 0;">
        <div style="background: #ff4757; color: white; padding: 12px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
            <span>Campus AI Assistant</span>
            <span onclick="toggleAIChat()" style="cursor: pointer; font-size: 1.2rem; line-height: 1;">&times;</span>
        </div>
        <div id="ai-chat-messages" style="flex: 1; padding: 12px; overflow-y: auto; font-size: 0.9rem; background: #fafafa;">
            <p style="background: #eee; padding: 8px 12px; border-radius: 8px; margin: 5px 0; color: #333;">Hello! I am active across your campus portal and food court. How can I help you today?</p>
        </div>
        <div style="padding: 10px; border-top: 1px solid #eee; display: flex; background: white;">
            <input type="text" id="ai-user-input" placeholder="Type a message..." style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none; font-size: 0.9rem;" onkeypress="handleKey(event)">
            <button onclick="sendAIMessage()" style="background: #ff4757; color: white; border: none; padding: 8px 14px; margin-left: 6px; border-radius: 4px; cursor: pointer; font-weight: bold;">Send</button>
        </div>
    </div>
</div>

<script>
    function toggleAIChat() {
        const box = document.getElementById('ai-chat-box');
        box.style.display = box.style.display === 'flex' ? 'none' : 'flex';
    }

    function handleKey(e) {
        if (e.key === 'Enter') sendAIMessage();
    }

    function sendAIMessage() {
        const input = document.getElementById('ai-user-input');
        const container = document.getElementById('ai-chat-messages');
        const query = input.value.trim();
        if (!query) return;

        container.innerHTML += `<div style="text-align: right; margin: 8px 0;"><span style="background: #1e90ff; color: white; padding: 8px 12px; border-radius: 8px; display: inline-block; max-width: 80%; text-align: left;">${query}</span></div>`;
        input.value = '';
        container.scrollTop = container.scrollHeight;

        setTimeout(() => {
            let responseText = "I'm tracking your request regarding '" + query + "'. Let me know if you need any info on active food orders or campus services!";
            if(query.toLowerCase().includes('token') || query.toLowerCase().includes('order') || query.toLowerCase().includes('time')) {
                responseText = "Your active food token status, IST prep times, and order details are dynamically logged and managed via your database!";
            }
            container.innerHTML += `<div style="text-align: left; margin: 8px 0;"><span style="background: #eee; color: #333; padding: 8px 12px; border-radius: 8px; display: inline-block; max-width: 80%;">${responseText}</span></div>`;
            container.scrollTop = container.scrollHeight;
        }, 500);
    }
</script>
