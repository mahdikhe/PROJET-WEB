// Script to fix API paths for AI features
console.log('=== Path Fixing Script ===');

// Function to create correct API paths based on current URL
function fixApiPaths() {
    const currentUrl = window.location.href;
    console.log('Current page URL:', currentUrl);
    
    // Determine if we're in frontoffice
    const inFrontoffice = currentUrl.includes('/frontoffice/');
    console.log('In frontoffice folder:', inFrontoffice);
    
    // Set the correct path for ai-chat.php
    window.AI_CHAT_ENDPOINT = inFrontoffice 
        ? '../../ai-chat.php'  // Path from frontoffice
        : '../ai-chat.php';    // Path from regular view folder
    
    console.log('AI Chat endpoint set to:', window.AI_CHAT_ENDPOINT);
    
    // Function to override generateChatResponse to use the correct path
    if (typeof window.generateChatResponse === 'function') {
        console.log('Overriding generateChatResponse to use correct path');
        
        // Store the original function
        const originalGenerateChatResponse = window.generateChatResponse;
        
        // Override with patched version
        window.generateChatResponse = async function(userMessage) {
            console.log('Using patched generateChatResponse with endpoint:', window.AI_CHAT_ENDPOINT);
            
            try {
                // Use our custom endpoint path
                const response = await fetch(window.AI_CHAT_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message: userMessage })
                });
                
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`Network response was not ok. Status: ${response.status}`);
                }
                
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                try {
                    const data = JSON.parse(responseText);
                    console.log('Parsed response:', data);
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    return data.response;
                } catch (parseError) {
                    console.error('Error parsing response:', parseError);
                    throw new Error('Could not parse response from server');
                }
            } catch (error) {
                console.error('Error in patched generateChatResponse:', error);
                return "Sorry, I'm having trouble connecting to the server. Please try again later.";
            }
        };
        
        console.log('generateChatResponse successfully patched');
    } else {
        console.error('generateChatResponse function not found, cannot patch');
    }
}

// Call the fix function when the page loads
document.addEventListener('DOMContentLoaded', fixApiPaths);

// Add test functions to the window object
window.testApiPath = async function() {
    console.log('Testing API connection to:', window.AI_CHAT_ENDPOINT || '../ai-chat.php');
    
    try {
        const response = await fetch(window.AI_CHAT_ENDPOINT || '../ai-chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: 'test' })
        });
        
        console.log('Response status:', response.status);
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        if (response.ok) {
            console.log('API connection successful!');
            return true;
        } else {
            console.error('API connection failed with status:', response.status);
            return false;
        }
    } catch (error) {
        console.error('Error testing API path:', error);
        return false;
    }
};

// Create a manual promo and chat test functions that use direct DOM elements
window.manualPromoTest = function() {
    console.log('Running manual promo test');
    
    const firstCard = document.querySelector('.event-card');
    if (!firstCard) {
        console.error('No event card found');
        return;
    }
    
    // Create a simple promo content manually to test the modal
    const promoContent = `🎉 Event Promotion Test 🎉

This is a test of the promotional content system.
If you see this, the modal is working but there might be an issue with the API connection.`;
    
    // Set the content and show the modal
    const promoContentEl = document.getElementById('promoContent');
    if (promoContentEl) {
        promoContentEl.textContent = promoContent;
        
        try {
            const modalEl = document.getElementById('promoModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                console.log('Modal displayed successfully');
            } else {
                console.error('Modal element not found');
            }
        } catch (error) {
            console.error('Error showing modal:', error);
        }
    } else {
        console.error('promoContent element not found');
    }
};

window.manualChatTest = function(message = "Hello!") {
    console.log('Running manual chat test with message:', message);
    
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) {
        console.error('Chat messages container not found');
        return;
    }
    
    // Add user message
    const userMsg = document.createElement('div');
    userMsg.className = 'chat-message user-message';
    userMsg.innerHTML = `<div class="message-content">${message}</div>`;
    chatMessages.appendChild(userMsg);
    
    // Add assistant response
    const assistantMsg = document.createElement('div');
    assistantMsg.className = 'chat-message assistant-message';
    assistantMsg.innerHTML = `<div class="message-content">This is a test response. The chat UI is working, but there might be an issue with the API connection.</div>`;
    chatMessages.appendChild(assistantMsg);
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
    console.log('Test messages added to chat');
};

console.log('Path fixing script loaded. The following test functions are available:');
console.log('- testApiPath(): Test API connection');
console.log('- manualPromoTest(): Test promo modal without API');
console.log('- manualChatTest(): Test chat UI without API');
console.log('=== End of Path Fixing Script ==='); 