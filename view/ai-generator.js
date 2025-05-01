// Function to generate promotional content for an event
async function generateEventPromo(eventCard) {
    console.log('=== Generating Event Promo ===');
    
    // Get event details from the card
    const eventId = eventCard.getAttribute('data-event-id');
    const eventTitle = eventCard.querySelector('.event-title').textContent.trim();
    const eventDate = eventCard.querySelector('.date-text').textContent.trim();
    const eventLocation = eventCard.querySelector('.location-text').textContent.trim();
    const eventFormat = eventCard.querySelector('.format-text').textContent.trim();
    const eventCapacity = eventCard.querySelector('.attendees-text').textContent.trim();
    
    console.log('Event Details:', {
        id: eventId,
        title: eventTitle,
        date: eventDate,
        location: eventLocation,
        format: eventFormat,
        capacity: eventCapacity
    });

    try {
        // Generate promotional content
        const promoContent = `🎉 Exciting Event Alert! 🎉

${eventTitle} is coming to ${eventLocation} on ${eventDate}!

Join us for this ${eventFormat} event that promises to be an unforgettable experience. With ${eventCapacity} available, don't miss your chance to be part of this amazing gathering.

What to expect:
• Engaging content and activities
• Networking opportunities
• Valuable insights and takeaways
• A chance to connect with like-minded individuals

Limited spots available - secure your place today!

#${eventTitle.replace(/\s+/g, '')} #Event #${eventFormat}Event`;

        // Read out the promotional content using ElevenLabs
        try {
            const response = await fetch('https://api.elevenlabs.io/v1/text-to-speech/JBFqnCBsd6RMkjVDRZzb', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'xi-api-key': 'sk_8bd227614b9e52aecce7d122dc69331fa70b41737a2fb5e8'
                },
                body: JSON.stringify({
                    text: promoContent,
                    model_id: "eleven_multilingual_v2",
                    voice_settings: {
                        stability: 0.5,
                        similarity_boost: 0.75
                    }
                })
            });

            if (response.ok) {
                const audioBlob = await response.blob();
                const audioUrl = URL.createObjectURL(audioBlob);
                const audio = new Audio(audioUrl);
                audio.play();
            }
        } catch (speechError) {
            console.error('Error playing speech:', speechError);
        }

        return promoContent;
    } catch (error) {
        console.error('Error generating promo content:', error);
        return null;
    }
}

// Initialize promo buttons when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add promo button to each event card
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        const actionsDiv = card.querySelector('.event-actions');
        if (actionsDiv) {
            const promoButton = document.createElement('button');
            promoButton.className = 'btn btn-warning btn-sm generate-promo-btn';
            promoButton.innerHTML = '<i class="fas fa-magic"></i> AI Promo';
            promoButton.addEventListener('click', async () => {
                // Show loading state
                promoButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                promoButton.disabled = true;
                
                try {
                    const promoContent = await generateEventPromo(card);
                    if (promoContent) {
                        // Display in modal
                        document.getElementById('promoContent').textContent = promoContent;
                        const modal = new bootstrap.Modal(document.getElementById('promoModal'));
                        modal.show();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error generating promotional content. Please try again.');
                } finally {
                    // Reset button state
                    promoButton.innerHTML = '<i class="fas fa-magic"></i> AI Promo';
                    promoButton.disabled = false;
                }
            });
            actionsDiv.appendChild(promoButton);
        }
    });
}); 