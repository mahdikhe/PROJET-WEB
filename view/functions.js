// Form Validation Functions
function validateField(input, type, options = {}) {
    const value = input.value.trim();
    let isValid = true;
    let errorMessage = "";

    switch(type) {
        case "name":
            if (value === "") {
                isValid = false;
                errorMessage = "Please enter your name";
            } else if (value.length < 2) {
                isValid = false;
                errorMessage = "Name must be at least 2 characters long";
            }
            break;
        case "email":
            if (value === "") {
                isValid = false;
                errorMessage = "Please enter your email address";
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                isValid = false;
                errorMessage = "Please enter a valid email address";
            }
            break;
        case "phone":
            if (value !== "" && !/^\+?[\d\s-]{8,}$/.test(value)) {
                isValid = false;
                errorMessage = "Please enter a valid phone number";
            }
            break;
        case "seats":
            const seats = parseInt(value);
            const maxSeats = options.maxSeats || 10;
            if (isNaN(seats) || seats < 1 || seats > maxSeats) {
                isValid = false;
                errorMessage = `Please select a valid number of seats (1-${maxSeats})`;
            }
            break;
        case "password":
            if (value === "") {
                isValid = false;
                errorMessage = "Please enter your password";
            } else if (value.length < 6) {
                isValid = false;
                errorMessage = "Password must be at least 6 characters long";
            }
            break;
        case "confirmPassword":
            const password = options.password || "";
            if (value !== password) {
                isValid = false;
                errorMessage = "Passwords do not match";
            }
            break;
        case "date":
            if (value === "") {
                isValid = false;
                errorMessage = "Please select a date";
            } else if (new Date(value) < new Date()) {
                isValid = false;
                errorMessage = "Please select a future date";
            }
            break;
        case "time":
            if (value === "") {
                isValid = false;
                errorMessage = "Please select a time";
            }
            break;
        case "url":
            if (value !== "" && !/^https?:\/\/.+\..+/.test(value)) {
                isValid = false;
                errorMessage = "Please enter a valid URL";
            }
            break;
        case "text":
            if (value === "") {
                isValid = false;
                errorMessage = "This field is required";
            } else if (options.minLength && value.length < options.minLength) {
                isValid = false;
                errorMessage = `Text must be at least ${options.minLength} characters long`;
            } else if (options.maxLength && value.length > options.maxLength) {
                isValid = false;
                errorMessage = `Text must be at most ${options.maxLength} characters long`;
            }
            break;
        case "number":
            if (value === "") {
                isValid = false;
                errorMessage = "Please enter a number";
            } else if (isNaN(value)) {
                isValid = false;
                errorMessage = "Please enter a valid number";
            } else if (options.min !== undefined && value < options.min) {
                isValid = false;
                errorMessage = `Value must be at least ${options.min}`;
            } else if (options.max !== undefined && value > options.max) {
                isValid = false;
                errorMessage = `Value must be at most ${options.max}`;
            }
            break;
    }

    if (!isValid) {
        input.classList.add("is-invalid");
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains("invalid-feedback")) {
            feedback.textContent = errorMessage;
            feedback.style.display = "block";
        }
        alert(errorMessage);
    } else {
        input.classList.remove("is-invalid");
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains("invalid-feedback")) {
            feedback.style.display = "none";
        }
    }

    return isValid;
}

// Form Validation Setup
function setupFormValidation(formId, validationRules) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Add event listeners for real-time validation
    Object.entries(validationRules).forEach(([fieldId, rules]) => {
        const input = document.getElementById(fieldId);
        if (input) {
            input.addEventListener("blur", () => validateField(input, rules.type, rules.options));
        }
    });

    // Form submission validation
    form.addEventListener("submit", function(e) {
        let isValid = true;
        const errorMessages = [];

        Object.entries(validationRules).forEach(([fieldId, rules]) => {
            const input = document.getElementById(fieldId);
            if (input && !validateField(input, rules.type, rules.options)) {
                isValid = false;
                errorMessages.push(input.getAttribute("data-error-message") || "Invalid input");
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert("Please fix the following errors:\n\n" + errorMessages.join("\n"));
        }
    });
}

// Seat Control Functions
function setupSeatControl(seatsInputId, decreaseBtnId, increaseBtnId, maxSeats, pricePerSeat) {
    const seatsInput = document.getElementById(seatsInputId);
    const decreaseBtn = document.getElementById(decreaseBtnId);
    const increaseBtn = document.getElementById(increaseBtnId);
    const seatCount = document.getElementById("seatCount");
    const totalPrice = document.getElementById("totalPrice");

    if (!seatsInput || !decreaseBtn || !increaseBtn) return;

    decreaseBtn.addEventListener("click", function() {
        let currentVal = parseInt(seatsInput.value);
        if (currentVal > 1) {
            seatsInput.value = currentVal - 1;
            updatePriceSummary();
            validateField(seatsInput, "seats", { maxSeats });
        }
    });

    increaseBtn.addEventListener("click", function() {
        let currentVal = parseInt(seatsInput.value);
        if (currentVal < maxSeats) {
            seatsInput.value = currentVal + 1;
            updatePriceSummary();
            validateField(seatsInput, "seats", { maxSeats });
        }
    });

    function updatePriceSummary() {
        if (seatCount && totalPrice) {
            const seats = parseInt(seatsInput.value);
            seatCount.textContent = seats;
            totalPrice.textContent = "$" + (seats * pricePerSeat).toFixed(2);
        }
    }
}

// Date and Time Validation
function setupDateTimeValidation(dateInputId, timeInputId) {
    const dateInput = document.getElementById(dateInputId);
    const timeInput = document.getElementById(timeInputId);

    if (dateInput) {
        dateInput.addEventListener("change", () => validateField(dateInput, "date"));
    }

    if (timeInput) {
        timeInput.addEventListener("change", () => validateField(timeInput, "time"));
    }
}


// QR Code Generation
function generateQRCode(eventId, eventTitle) {
    console.log('=== QR Code Generation Debug ===');
    console.log('Event ID:', eventId);
    console.log('Event Title:', eventTitle);
    console.log('QRCode library available:', typeof QRCode !== 'undefined');
    
    // Check if QRCode library is available
    if (typeof QRCode === 'undefined') {
        console.error('QRCode library not loaded!');
        alert('QR Code library not loaded. Please refresh the page.');
        return;
    }
    
    // Remove any existing QR modal
    const existingModal = document.querySelector('.qr-modal');
    if (existingModal) {
        console.log('Removing existing modal');
        existingModal.remove();
    }
    
    // Get the button that was clicked
    const clickedButton = document.querySelector(`.show-qr-btn[data-event-id="${eventId}"]`);
    console.log('Clicked Button:', clickedButton);
    
    // Get event details from the button's data attributes
    const eventDate = clickedButton?.getAttribute('data-event-date') || 'Date not specified';
    const eventLocation = clickedButton?.getAttribute('data-event-location') || 'Location not specified';
    const eventFormat = clickedButton?.getAttribute('data-event-format') || 'Format not specified';
    const eventCapacity = clickedButton?.getAttribute('data-event-capacity') || 'Capacity not specified';
    
    console.log('Event Details:', {
        date: eventDate,
        location: eventLocation,
        format: eventFormat,
        capacity: eventCapacity
    });
    
    // Create a human-readable text format for the QR code
    const qrText = `
EVENT INFORMATION
----------------
Title: ${eventTitle}
ID: ${eventId}
Date: ${eventDate}
Location: ${eventLocation}
Format: ${eventFormat}

----------------

    `.trim();
    
    console.log('QR Text:', qrText);
    
    // Create QR code element
    const qrContainer = document.createElement('div');
    qrContainer.className = 'qr-modal';
    qrContainer.innerHTML = `
        <div class="qr-content">
            <div class="qr-header">
                <h3>${eventTitle}</h3>
                <button class="close-qr">&times;</button>
            </div>
            <div class="qr-details">
                <div class="qr-detail">
                    <i class="fas fa-calendar"></i>
                    <span>${eventDate}</span>
                </div>
                <div class="qr-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${eventLocation}</span>
                </div>
                
            </div>
            <div class="qr-body">
                <div id="qr-code-${eventId}" class="qr-code"></div>
            </div>
            <div class="qr-footer">
                <button class="btn btn-primary download-qr">
                    <i class="fas fa-download"></i> Download QR Code
                </button>
            </div>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(qrContainer);
    console.log('Modal added to document');
    
    // Generate QR code using QRCode.js
    try {
        const qrElement = document.getElementById(`qr-code-${eventId}`);
        console.log('QR Element found:', qrElement);
        
        if (qrElement) {
            // Clear any existing QR code
            qrElement.innerHTML = '';
            
            // Create new QR code
            console.log('Creating new QR code...');
            new QRCode(qrElement, {
                text: qrText,
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            
            console.log('QR code generated successfully');
        } else {
            console.error('QR code element not found');
            alert('Error: Could not create QR code element');
        }
    } catch (error) {
        console.error('Error generating QR code:', error);
        alert('Error generating QR code: ' + error.message);
    }
    
    // Add event listeners
    const closeButton = qrContainer.querySelector('.close-qr');
    const downloadButton = qrContainer.querySelector('.download-qr');
    
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            console.log('Close button clicked');
            qrContainer.remove();
        });
    } else {
        console.error('Close button not found');
    }
    
    if (downloadButton) {
        downloadButton.addEventListener('click', () => {
            console.log('Download button clicked');//
            const canvas = qrContainer.querySelector(`#qr-code-${eventId} canvas`);//na5v tswira
            if (canvas) {
                const link = document.createElement('a');// n3mal link SABAN
                link.download = `event-qr-${eventId}.png`;//N7T ISM
                link.href = canvas.toDataURL();//yabda ysob
                link.click();
            } else {
                console.error('Canvas not found for download');
            }
        });
    } else {
        console.error('Download button not found');
    }
    
    // Close on click outside
    qrContainer.addEventListener('click', (e) => {
        if (e.target === qrContainer) {
            console.log('Modal background clicked');
            qrContainer.remove();
        }
    });
}

// Initialize QR code functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== QR Code Initialization ===');
    
    // Check if QRCode library is available
    if (typeof QRCode === 'undefined') {
        console.error('QRCode library not loaded during initialization!');
        return;
    }
    
    // Add event listeners to all QR code buttons
    const qrButtons = document.querySelectorAll('.show-qr-btn');
    console.log('Number of QR buttons found:', qrButtons.length);
    
    qrButtons.forEach((button, index) => {
        console.log(`Setting up button ${index + 1}:`, button);
        const eventId = button.getAttribute('data-event-id');
        const eventTitle = button.getAttribute('data-event-title');
        
        if (!eventId || !eventTitle) {
            console.error(`Button ${index + 1} missing required data attributes:`, { eventId, eventTitle });
            return;
        }
        
        button.addEventListener('click', function(e) {
            // If the button is an anchor tag with href, don't prevent default
            if (button.tagName.toLowerCase() === 'a' && button.getAttribute('href')) {
                return;
            }
            
            e.preventDefault();
            console.log(`Button ${index + 1} clicked`);
            generateQRCode(eventId, eventTitle);
        });
    });


});

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
        // Import the AI generator
        const { generateEventDescription } = await import('./ai-generator.js');
        
        // Generate promotional content
        const promoContent = await generateEventDescription({
            title: eventTitle,
            date: eventDate,
            location: eventLocation,
            format: eventFormat,
            capacity: eventCapacity
        });
        
        console.log('Generated Promo Content:', promoContent);
        return promoContent;
    } catch (error) {
        console.error('Error generating promo content:', error);
        return null;
    }
}

// Add event listener for promo generation
document.addEventListener('DOMContentLoaded', function() {
    // Add promo button to each event card
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        const actionsDiv = card.querySelector('.event-actions');
        if (actionsDiv) {
            const promoButton = document.createElement('button');
            promoButton.className = 'btn btn-warning btn-sm generate-promo-btn';
            promoButton.innerHTML = '<i class="fas fa-magic"></i> Generate Promo';
            promoButton.addEventListener('click', async () => {
                const promoContent = await generateEventPromo(card);
                if (promoContent) {
                    // You can also display this in a modal or alert if desired
                    alert('Promotional content generated! Check the console for details.');
                }
            });
            actionsDiv.appendChild(promoButton);
        }
    });
}); 