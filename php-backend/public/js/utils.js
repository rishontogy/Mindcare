/**
 * Main JavaScript Utilities
 */

// DOM Utilities
const DOM = {
    id: (id) => document.getElementById(id),
    query: (selector) => document.querySelector(selector),
    queryAll: (selector) => document.querySelectorAll(selector),
    create: (tag, classes = '') => {
        const el = document.createElement(tag);
        if (classes) el.className = classes;
        return el;
    },
    addClass: (el, className) => el && el.classList.add(className),
    removeClass: (el, className) => el && el.classList.remove(className),
    toggleClass: (el, className) => el && el.classList.toggle(className),
    hasClass: (el, className) => el && el.classList.contains(className),
    show: (el) => el && (el.style.display = 'block'),
    hide: (el) => el && (el.style.display = 'none'),
    toggle: (el) => {
        if (el) {
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }
    }
};

// API Utilities
const API = {
    baseURL: '${APP_URL}/api',
    
    request: async (method, endpoint, data = null) => {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (data) {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(API.baseURL + endpoint, options);
            const responseData = await response.json();
            
            if (!response.ok) {
                throw new Error(responseData.message || 'An error occurred');
            }
            
            return responseData;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    get: (endpoint) => API.request('GET', endpoint),
    post: (endpoint, data) => API.request('POST', endpoint, data),
    put: (endpoint, data) => API.request('PUT', endpoint, data),
    delete: (endpoint) => API.request('DELETE', endpoint)
};

// Validation Utilities
const Validator = {
    email: (email) => {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    password: (password) => {
        // At least 8 characters, 1 uppercase, 1 digit
        const re = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
        return re.test(password);
    },
    
    string: (str, minLength = 2, maxLength = 255) => {
        const length = str.trim().length;
        return length >= minLength && length <= maxLength;
    },
    
    number: (num, min = 0, max = Number.MAX_SAFE_INTEGER) => {
        const n = Number(num);
        return !isNaN(n) && n >= min && n <= max;
    },
    
    required: (value) => {
        return value !== null && value !== undefined && value !== '';
    }
};

// Form Utilities
const Form = {
    clear: (formId) => {
        const form = DOM.id(formId);
        if (form) form.reset();
    },
    
    getFormData: (formId) => {
        const form = DOM.id(formId);
        if (!form) return null;
        
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    },
    
    showError: (inputId, message) => {
        const input = DOM.id(inputId);
        if (input) {
            DOM.addClass(input, 'error');
            const errorEl = DOM.query(`#${inputId}_error`);
            if (errorEl) {
                errorEl.textContent = message;
                DOM.removeClass(errorEl, 'hidden');
            }
        }
    },
    
    clearError: (inputId) => {
        const input = DOM.id(inputId);
        if (input) {
            DOM.removeClass(input, 'error');
            const errorEl = DOM.query(`#${inputId}_error`);
            if (errorEl) {
                DOM.addClass(errorEl, 'hidden');
                errorEl.textContent = '';
            }
        }
    },
    
    clearAllErrors: (formId) => {
        const form = DOM.id(formId);
        if (form) {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                Form.clearError(input.id);
            });
        }
    }
};

// Local Storage Utilities
const Storage = {
    set: (key, value) => {
        localStorage.setItem(key, JSON.stringify(value));
    },
    
    get: (key) => {
        const value = localStorage.getItem(key);
        return value ? JSON.parse(value) : null;
    },
    
    remove: (key) => {
        localStorage.removeItem(key);
    },
    
    clear: () => {
        localStorage.clear();
    }
};

// Notification Utilities
const Notify = {
    success: (message, duration = 3000) => {
        Notify._show(message, 'success', duration);
    },
    
    error: (message, duration = 3000) => {
        Notify._show(message, 'danger', duration);
    },
    
    warning: (message, duration = 3000) => {
        Notify._show(message, 'warning', duration);
    },
    
    info: (message, duration = 3000) => {
        Notify._show(message, 'info', duration);
    },
    
    _show: (message, type, duration) => {
        const alertEl = DOM.create('div', `alert alert-${type}`);
        alertEl.textContent = message;
        alertEl.style.position = 'fixed';
        alertEl.style.top = '20px';
        alertEl.style.right = '20px';
        alertEl.style.zIndex = '9999';
        alertEl.style.maxWidth = '400px';
        
        document.body.appendChild(alertEl);
        
        setTimeout(() => {
            alertEl.remove();
        }, duration);
    }
};

// Date Utilities
const DateUtils = {
    format: (date, format = 'YYYY-MM-DD') => {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const seconds = String(d.getSeconds()).padStart(2, '0');
        
        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day)
            .replace('HH', hours)
            .replace('mm', minutes)
            .replace('ss', seconds);
    },
    
    getToday: () => {
        return DateUtils.format(new Date(), 'YYYY-MM-DD');
    },
    
    addDays: (date, days) => {
        const d = new Date(date);
        d.setDate(d.getDate() + days);
        return d;
    }
};

// Loading Spinner
const Spinner = {
    show: (elementId) => {
        const el = DOM.id(elementId);
        if (el) {
            DOM.addClass(el, 'spinner');
        }
    },
    
    hide: (elementId) => {
        const el = DOM.id(elementId);
        if (el) {
            DOM.removeClass(el, 'spinner');
        }
    }
};

// Modal Utilities
const Modal = {
    open: (modalId) => {
        const modal = DOM.id(modalId);
        if (modal) {
            DOM.addClass(modal, 'active');
        }
    },
    
    close: (modalId) => {
        const modal = DOM.id(modalId);
        if (modal) {
            DOM.removeClass(modal, 'active');
        }
    },
    
    toggle: (modalId) => {
        const modal = DOM.id(modalId);
        if (modal) {
            DOM.toggleClass(modal, 'active');
        }
    }
};

// Common event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Close modals on background click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                Modal.close(modal.id);
            }
        });
    });
    
    // Form submission
    document.querySelectorAll('form[data-ajax="true"]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = Form.getFormData(form.id);
            const action = form.getAttribute('action');
            const method = form.getAttribute('method') || 'POST';
            
            try {
                const result = await API.request(method, action, formData);
                if (result.success) {
                    Notify.success(result.message);
                    Form.clear(form.id);
                }
            } catch (error) {
                Notify.error(error.message);
            }
        });
    });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { DOM, API, Validator, Form, Storage, Notify, DateUtils, Spinner, Modal };
}
