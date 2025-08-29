document.addEventListener('DOMContentLoaded', function() {
    // Handle smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                // Remove active class from all nav items
                document.querySelectorAll('.list-group-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Add active class to clicked nav item
                this.classList.add('active');
                
                // Scroll to the target element
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update URL without page reload
                history.pushState(null, '', targetId);
            }
        });
    });
    
    // Highlight the active section in the sidebar based on scroll position
    const sections = document.querySelectorAll('section[id], div[id]');
    const navItems = document.querySelectorAll('.list-group-item[href^="#"]');
    
    function highlightNavItem() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (window.scrollY >= sectionTop - 200) {
                current = '#' + section.getAttribute('id');
            }
        });
        
        navItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('href') === current) {
                item.classList.add('active');
            }
        });
    }
    
    // Highlight on scroll
    window.addEventListener('scroll', highlightNavItem);
    
    // Highlight on page load based on URL hash
    if (window.location.hash) {
        const target = document.querySelector(window.location.hash);
        if (target) {
            target.scrollIntoView();
        }
    }
    
    // Initialize highlight
    highlightNavItem();
});
