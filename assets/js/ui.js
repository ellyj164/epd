/**
 * Modern UI Components and Interactions
 * E-Commerce Platform Frontend
 */

// Main UI Application
class UI {
    constructor() {
        this.init();
    }

    init() {
        // Initialize all modules when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeModules();
            });
        } else {
            this.initializeModules();
        }
    }

    initializeModules() {
        // Initialize all UI modules
        Toast.init();
        CartDrawer.init();
        SkeletonLoader.init();
        Navigation.init();
        Forms.init();
        ProductCard.init();
        LazyImages.init();
    }
}

// Toast Notification System
class Toast {
    static init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-4';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }
    }

    static show(message, type = 'info', duration = 4000) {
        const toast = document.createElement('div');
        const icons = {
            success: '✓',
            error: '✗',
            warning: '⚠',
            info: 'ℹ'
        };

        toast.className = `toast transform translate-x-full transition-transform duration-300 ease-out
            flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg max-w-md ${this.getTypeClasses(type)}`;
        
        toast.innerHTML = `
            <span class="toast-icon text-lg" aria-hidden="true">${icons[type] || icons.info}</span>
            <span class="toast-message flex-1 text-sm font-medium">${message}</span>
            <button class="toast-close ml-2 text-lg opacity-70 hover:opacity-100 focus:outline-none" aria-label="Close notification">×</button>
        `;

        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full');
        });

        // Auto remove
        const autoRemove = setTimeout(() => this.remove(toast), duration);

        // Manual close
        toast.querySelector('.toast-close').addEventListener('click', () => {
            clearTimeout(autoRemove);
            this.remove(toast);
        });
    }

    static getTypeClasses(type) {
        const classes = {
            success: 'bg-green-50 text-green-800 border border-green-200',
            error: 'bg-red-50 text-red-800 border border-red-200',
            warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
            info: 'bg-blue-50 text-blue-800 border border-blue-200'
        };
        return classes[type] || classes.info;
    }

    static remove(toast) {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

// Cart Drawer Component
class CartDrawer {
    static init() {
        this.isOpen = false;
        this.createDrawer();
        this.bindEvents();
    }

    static createDrawer() {
        if (document.getElementById('cart-drawer')) return;

        const drawer = document.createElement('div');
        drawer.id = 'cart-drawer';
        drawer.className = 'fixed inset-0 z-50 hidden';
        
        drawer.innerHTML = `
            <!-- Backdrop -->
            <div class="cart-backdrop fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
            
            <!-- Drawer -->
            <div class="cart-panel fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-xl transform translate-x-full transition-transform">
                <div class="flex flex-col h-full">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b">
                        <h2 class="text-lg font-semibold text-neutral-800">Shopping Cart</h2>
                        <button class="cart-close p-2 hover:bg-neutral-100 rounded-lg focus:outline-none focus:ring">
                            <span class="sr-only">Close cart</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Cart Items -->
                    <div class="flex-1 overflow-y-auto p-6">
                        <div id="cart-items-container">
                            <!-- Items will be loaded here -->
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="border-t p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-lg font-semibold">Total:</span>
                            <span class="text-xl font-bold text-primary-600" id="cart-total">$0.00</span>
                        </div>
                        <button class="btn btn-primary w-full">Proceed to Checkout</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(drawer);
    }

    static bindEvents() {
        // Cart open triggers
        document.addEventListener('click', (e) => {
            if (e.target.matches('.cart-toggle, .cart-toggle *')) {
                e.preventDefault();
                this.open();
            }
        });

        // Cart close triggers
        const drawer = document.getElementById('cart-drawer');
        drawer.addEventListener('click', (e) => {
            if (e.target.matches('.cart-backdrop, .cart-close')) {
                this.close();
            }
        });

        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    }

    static open() {
        const drawer = document.getElementById('cart-drawer');
        const panel = drawer.querySelector('.cart-panel');
        
        drawer.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
        });
        
        this.isOpen = true;
        this.loadCartItems();
    }

    static close() {
        const drawer = document.getElementById('cart-drawer');
        const panel = drawer.querySelector('.cart-panel');
        
        panel.classList.add('translate-x-full');
        document.body.style.overflow = '';
        
        setTimeout(() => {
            drawer.classList.add('hidden');
        }, 300);
        
        this.isOpen = false;
    }

    static async loadCartItems() {
        // Placeholder for cart loading logic
        console.log('Loading cart items...');
    }
}

// Skeleton Loader Component
class SkeletonLoader {
    static init() {
        // Add skeleton CSS if not present
        this.addSkeletonStyles();
    }

    static addSkeletonStyles() {
        if (document.getElementById('skeleton-styles')) return;

        const style = document.createElement('style');
        style.id = 'skeleton-styles';
        style.textContent = `
            .skeleton {
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
                animation: loading 1.5s infinite;
            }
            
            @keyframes loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
            
            .skeleton-text {
                height: 1rem;
                border-radius: 4px;
            }
            
            .skeleton-title {
                height: 1.5rem;
                border-radius: 4px;
                margin-bottom: 0.5rem;
            }
            
            .skeleton-image {
                width: 100%;
                height: 200px;
                border-radius: 8px;
            }
        `;

        document.head.appendChild(style);
    }

    static create(type = 'text', className = '') {
        const skeleton = document.createElement('div');
        skeleton.className = `skeleton skeleton-${type} ${className}`;
        return skeleton;
    }

    static show(container) {
        container.classList.add('loading');
        // Add skeleton elements based on content type
    }

    static hide(container) {
        container.classList.remove('loading');
        container.querySelectorAll('.skeleton').forEach(el => el.remove());
    }
}

// Navigation Enhancements
class Navigation {
    static init() {
        this.initStickyHeader();
        this.initMobileMenu();
        this.initMegaMenu();
    }

    static initStickyHeader() {
        const header = document.querySelector('header, .header, .navbar');
        if (!header) return;

        let lastScrollY = window.scrollY;
        
        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            
            if (currentScrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            // Hide/show on scroll
            if (currentScrollY > lastScrollY && currentScrollY > 200) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            
            lastScrollY = currentScrollY;
        });
    }

    static initMobileMenu() {
        const toggles = document.querySelectorAll('.mobile-menu-toggle');
        const menu = document.querySelector('.mobile-menu');
        
        if (!menu) return;

        toggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                menu.classList.toggle('open');
                document.body.classList.toggle('menu-open');
            });
        });
    }

    static initMegaMenu() {
        const menuItems = document.querySelectorAll('.mega-menu-item');
        
        menuItems.forEach(item => {
            const submenu = item.querySelector('.mega-menu-content');
            if (!submenu) return;

            let timeout;
            
            item.addEventListener('mouseenter', () => {
                clearTimeout(timeout);
                submenu.classList.add('open');
            });
            
            item.addEventListener('mouseleave', () => {
                timeout = setTimeout(() => {
                    submenu.classList.remove('open');
                }, 150);
            });
        });
    }
}

// Form Enhancements
class Forms {
    static init() {
        this.initInlineValidation();
        this.initInputMasks();
    }

    static initInlineValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });
            
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    }

    static validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';

        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'This field is required';
        }

        // Email validation
        if (field.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        }

        // Min length validation
        const minLength = field.getAttribute('data-min-length');
        if (minLength && value.length < parseInt(minLength)) {
            isValid = false;
            message = `Minimum ${minLength} characters required`;
        }

        this.showFieldError(field, isValid ? null : message);
        return isValid;
    }

    static validateForm(form) {
        const fields = form.querySelectorAll('input, textarea, select');
        let isValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    static showFieldError(field, message) {
        this.clearFieldError(field);

        if (message) {
            field.classList.add('error');
            
            const errorElement = document.createElement('div');
            errorElement.className = 'field-error text-sm text-red-600 mt-2';
            errorElement.textContent = message;
            
            field.parentNode.appendChild(errorElement);
        }
    }

    static clearFieldError(field) {
        field.classList.remove('error');
        const error = field.parentNode.querySelector('.field-error');
        if (error) {
            error.remove();
        }
    }

    static isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    static initInputMasks() {
        // Phone number masking
        const phoneInputs = document.querySelectorAll('input[data-mask="phone"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                e.target.value = value;
            });
        });

        // Credit card masking
        const ccInputs = document.querySelectorAll('input[data-mask="creditcard"]');
        ccInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                e.target.value = value;
            });
        });
    }
}

// Product Card Component
class ProductCard {
    static init() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-cart-btn')) {
                e.preventDefault();
                this.handleAddToCart(e.target);
            }
        });
    }

    static async handleAddToCart(button) {
        const productId = button.getAttribute('data-product-id');
        const quantity = 1;

        // Show loading state
        button.disabled = true;
        button.textContent = 'Adding...';

        try {
            // API call would go here
            await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate API delay
            
            // Show success
            Toast.show('Product added to cart!', 'success');
            button.textContent = 'Added!';
            
            setTimeout(() => {
                button.disabled = false;
                button.textContent = 'Add to Cart';
            }, 2000);
            
        } catch (error) {
            Toast.show('Failed to add product to cart', 'error');
            button.disabled = false;
            button.textContent = 'Add to Cart';
        }
    }
}

// Lazy Image Loading
class LazyImages {
    static init() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(this.handleIntersection.bind(this));
            this.observeImages();
        } else {
            // Fallback for older browsers
            this.loadAllImages();
        }
    }

    static observeImages() {
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => this.observer.observe(img));
    }

    static handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                this.loadImage(img);
                this.observer.unobserve(img);
            }
        });
    }

    static loadImage(img) {
        const src = img.getAttribute('data-src');
        const srcset = img.getAttribute('data-srcset');
        
        if (src) {
            img.src = src;
            img.removeAttribute('data-src');
        }
        
        if (srcset) {
            img.srcset = srcset;
            img.removeAttribute('data-srcset');
        }
        
        img.classList.add('loaded');
    }

    static loadAllImages() {
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => this.loadImage(img));
    }
}

// Initialize the UI when script loads
const app = new UI();

// Export for global access
window.UI = {
    Toast,
    CartDrawer,
    SkeletonLoader,
    Navigation,
    Forms,
    ProductCard,
    LazyImages
};