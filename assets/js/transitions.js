// transitions.js
document.addEventListener('DOMContentLoaded', function() {
    // Create transition element
    const transitionEl = document.createElement('div');
    transitionEl.className = 'page-transition';
    document.body.appendChild(transitionEl);
    
    // Handle all internal links
    document.querySelectorAll('a[href^="/"], form').forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't intercept external links or links with special classes
            if (this.href && !this.href.includes(window.location.host) || 
                this.classList.contains('no-transition')) {
                return;
            }
            
            e.preventDefault();
            const destination = this.href || this.getAttribute('action');
            
            // Start transition
            document.body.classList.add('transition-active');
            transitionEl.classList.add('in');
            
            // Delay navigation to allow animation to play
            setTimeout(() => {
                if (this.tagName === 'FORM') {
                    this.submit();
                } else {
                    window.location.href = destination;
                }
            }, 500);
        });
    });
    
    // Handle page load
    window.addEventListener('load', function() {
        const transitionEl = document.querySelector('.page-transition');
        if (transitionEl) {
            transitionEl.classList.remove('in');
            setTimeout(() => {
                transitionEl.remove();
                document.body.classList.remove('transition-active');
            }, 500);
        }
    });
});