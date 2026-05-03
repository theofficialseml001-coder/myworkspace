/**
 * PressWP - WordPress Clone
 * assets/js/main.js - Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var targetId = this.getAttribute('href');
            if (targetId !== '#' && targetId !== '#!') {
                var targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Navbar scroll effect
    var navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
    
    // Form validation enhancement
    var forms = document.querySelectorAll('form[novalidate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Alert auto-dismiss
    var alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        var dismissBtn = alert.querySelector('[data-bs-dismiss="alert"]');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function() {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            });
        }
    });
    
    // Lazy loading images
    var lazyImages = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        var imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    }
    
    // Back to top button
    var backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Search toggle
    var searchToggle = document.querySelector('.search-toggle');
    var searchForm = document.querySelector('.search-form');
    if (searchToggle && searchForm) {
        searchToggle.addEventListener('click', function(e) {
            e.preventDefault();
            searchForm.classList.toggle('active');
            searchForm.querySelector('input').focus();
        });
    }
    
    // Mobile menu toggle enhancement
    var navbarToggler = document.querySelector('.navbar-toggler');
    var navbarCollapse = document.querySelector('.navbar-collapse');
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navbarToggler.contains(e.target) && !navbarCollapse.contains(e.target)) {
                navbarCollapse.classList.remove('show');
            }
        });
    }
    
    // Card hover effects
    var cards = document.querySelectorAll('.card');
    cards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            cards.forEach(function(c) {
                if (c !== card) {
                    c.style.opacity = '0.8';
                }
            });
        });
        
        card.addEventListener('mouseleave', function() {
            cards.forEach(function(c) {
                c.style.opacity = '1';
            });
        });
    });
    
    // Copy to clipboard functionality
    var copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var textToCopy = this.dataset.copy;
            navigator.clipboard.writeText(textToCopy).then(function() {
                var originalText = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(function() {
                    btn.textContent = originalText;
                }, 2000);
            });
        });
    });
    
    // Tab switching
    var tabLinks = document.querySelectorAll('[data-tab]');
    tabLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var tabId = this.dataset.tab;
            
            // Remove active class from all tabs
            document.querySelectorAll('[data-tab]').forEach(function(t) {
                t.classList.remove('active');
            });
            
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(function(content) {
                content.classList.remove('active');
            });
            
            // Activate clicked tab
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Accordion functionality
    var accordionHeaders = document.querySelectorAll('.accordion-header');
    accordionHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            var content = this.nextElementSibling;
            var isExpanded = content.classList.contains('show');
            
            // Close all accordions
            document.querySelectorAll('.accordion-content').forEach(function(c) {
                c.classList.remove('show');
            });
            
            // Toggle current accordion
            if (!isExpanded) {
                content.classList.add('show');
            }
        });
    });
    
    // Modal functionality
    var modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    modalTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function() {
            var modalId = this.dataset.bsTarget;
            var modal = document.querySelector(modalId);
            if (modal) {
                modal.classList.add('show');
                modal.style.display = 'block';
                document.body.classList.add('modal-open');
            }
        });
    });
    
    var modalCloses = document.querySelectorAll('.modal .close, .modal [data-bs-dismiss="modal"]');
    modalCloses.forEach(function(close) {
        close.addEventListener('click', function() {
            var modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        });
    });
    
    // Tooltip initialization (simple version)
    var tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipElements.forEach(function(el) {
        el.addEventListener('mouseenter', function() {
            var title = this.dataset.bsTitle || this.title;
            if (title) {
                var tooltip = document.createElement('div');
                tooltip.className = 'tooltip show';
                tooltip.textContent = title;
                tooltip.style.position = 'absolute';
                tooltip.style.background = '#333';
                tooltip.style.color = '#fff';
                tooltip.style.padding = '5px 10px';
                tooltip.style.borderRadius = '4px';
                tooltip.style.fontSize = '12px';
                tooltip.style.zIndex = '1000';
                document.body.appendChild(tooltip);
                
                var rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
                tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
                
                this._tooltip = tooltip;
            }
        });
        
        el.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
    
    console.log('PressWP initialized successfully!');
});

// Utility functions
function PressWPUtils() {
    return {
        debounce: function(func, wait) {
            var timeout;
            return function executedFunction() {
                var context = this;
                var args = arguments;
                var later = function() {
                    timeout = null;
                    func.apply(context, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        throttle: function(func, limit) {
            var inThrottle;
            return function() {
                var context = this;
                var args = arguments;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(function() {
                        inThrottle = false;
                    }, limit);
                }
            };
        },
        
        formatDate: function(dateString) {
            var date = new Date(dateString);
            var options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        },
        
        truncate: function(str, length) {
            if (str.length <= length) return str;
            return str.substring(0, length) + '...';
        }
    };
}
