// Configurazione API
const API_URL = 'http://127.0.0.1:8000/api';

// Mostra errore su campo specifico
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Aggiungi bordo rosso
    field.classList.add('is-invalid');
    
    // Rimuovi errore precedente se esiste
    const existingError = field.parentElement.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Crea messaggio errore
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.style.display = 'block';
    errorDiv.textContent = message;
    
    // Inserisci dopo il campo
    field.parentElement.appendChild(errorDiv);
}

// Rimuovi tutti gli errori
function clearFieldErrors() {
    document.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(error => {
        error.remove();
    });
}

// Toast per messaggi generali (successo/errore globale)
function showToast(message, type = 'success') {
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(t => t.remove());

    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b'
    };

    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠'
    };

    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-left: 4px solid ${colors[type]};
        z-index: 99999;
        min-width: 300px;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;

    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: ${colors[type]};
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 18px;
            ">${icons[type]}</div>
            <div style="flex: 1; color: #1f2937; font-size: 15px;">${message}</div>
            <button onclick="this.parentElement.parentElement.remove()" style="
                background: none;
                border: none;
                color: #9ca3af;
                cursor: pointer;
                font-size: 24px;
                padding: 0;
                line-height: 1;
            ">×</button>
        </div>
    `;

    if (!document.getElementById('toast-animations')) {
        const style = document.createElement('style');
        style.id = 'toast-animations';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(400px)';
        toast.style.transition = 'all 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Gestione bottone loading (migliore)
function setButtonLoading(button, loading) {
    if (loading) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Caricamento...
        `;
        button.disabled = true;
    } else {
        button.innerHTML = button.dataset.originalText;
        button.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Form registrazione
    const signUpForm = document.getElementById('signUpForm');
    if (signUpForm) {
        // Rimuovi errori quando l'utente inizia a digitare
        signUpForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const error = this.parentElement.querySelector('.invalid-feedback');
                if (error) error.remove();
            });
        });

        signUpForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            clearFieldErrors();
            
            const submitBtn = signUpForm.querySelector('button[type="submit"]');
            setButtonLoading(submitBtn, true);
            
            // Verifica conferma password
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirmation').value;
            
            if (password !== passwordConfirm) {
                showFieldError('password_confirmation', 'Le password non coincidono');
                setButtonLoading(submitBtn, false);
                return;
            }
            
            const formData = {
                name: document.getElementById('name').value.trim(),
                surname: document.getElementById('surname').value.trim(),
                email: document.getElementById('email').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                password: password,
                password_confirmation: passwordConfirm
            };
            
            try {
                const response = await fetch(`${API_URL}/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (response.ok && response.status === 201) {
                    showToast('Registrazione completata! Reindirizzamento in corso...', 'success');
                    
                    setTimeout(() => {
                        window.location.href = 'sign-in.html';
                    }, 1500);
                } else {
                    if (response.status === 422 && data.errors) {
                        // Mostra errori sui campi specifici
                        for (let field in data.errors) {
                            showFieldError(field, data.errors[field][0]);
                        }
                    } else if (data.message) {
                        showToast(data.message, 'error');
                    } else {
                        showToast('Si è verificato un errore. Riprova più tardi.', 'error');
                    }
                    setButtonLoading(submitBtn, false);
                }
            } catch (error) {
                console.error('Errore:', error);
                showToast('Errore di connessione. Controlla la tua connessione internet.', 'error');
                setButtonLoading(submitBtn, false);
            }
        });
    }
    
    // Form login
    const signInForm = document.getElementById('signInForm');
    if (signInForm) {
        // Rimuovi errori quando l'utente inizia a digitare
        signInForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const error = this.parentElement.querySelector('.invalid-feedback');
                if (error) error.remove();
            });
        });

        signInForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            clearFieldErrors();
            
            const submitBtn = signInForm.querySelector('button[type="submit"]');
            setButtonLoading(submitBtn, true);
            
            const formData = {
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value
            };
            
            try {
                const response = await fetch(`${API_URL}/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    
                    showToast(`Benvenuto ${data.user.name}!`, 'success');
                    
                    setTimeout(() => {
                        window.location.href = 'index.html';
                    }, 1000);
                } else {
                    if (response.status === 422 && data.errors) {
                        for (let field in data.errors) {
                            showFieldError(field, data.errors[field][0]);
                        }
                    } else {
                        showFieldError('email', data.message || 'Email o password non corretti');
                    }
                    setButtonLoading(submitBtn, false);
                }
            } catch (error) {
                console.error('Errore:', error);
                showToast('Errore di connessione. Controlla la tua connessione internet.', 'error');
                setButtonLoading(submitBtn, false);
            }
        });
    }

    // Form forgot password
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            clearFieldErrors();

            const submitBtn = forgotPasswordForm.querySelector('button[type=\"submit\"]');
            setButtonLoading(submitBtn, true);

            const email = document.getElementById('email').value.trim();

            try {
                const response = await fetch(`${API_URL}/forgot-password`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();

                if (response.ok) {
                    showToast(data.message || 'Email di recupero inviata', 'success');
                } else {
                    if (response.status === 422 && data.errors) {
                        showFieldError('email', data.errors.email?.[0] || 'Email non valida');
                    } else {
                        showToast(data.message || 'Errore durante l\'invio', 'error');
                    }
                }
            } catch (error) {
                console.error('Errore:', error);
                showToast('Errore di connessione', 'error');
            } finally {
                setButtonLoading(submitBtn, false);
            }
        });
    }

    // Form reset password
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    if (resetPasswordForm) {
        const params = new URLSearchParams(window.location.search);
        const token = params.get('token');
        const email = params.get('email');

        if (token) {
            const tokenField = document.getElementById('resetToken');
            if (tokenField) tokenField.value = token;
        }
        if (email) {
            const emailField = document.getElementById('resetEmail');
            if (emailField) emailField.value = email;
        }

        resetPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            clearFieldErrors();

            const submitBtn = resetPasswordForm.querySelector('button[type=\"submit\"]');
            setButtonLoading(submitBtn, true);

            const payload = {
                token: document.getElementById('resetToken').value.trim(),
                email: document.getElementById('resetEmail').value.trim(),
                password: document.getElementById('resetPassword').value,
                password_confirmation: document.getElementById('resetPasswordConfirm').value
            };

            if (payload.password !== payload.password_confirmation) {
                showFieldError('resetPasswordConfirm', 'Le password non coincidono');
                setButtonLoading(submitBtn, false);
                return;
            }

            try {
                const response = await fetch(`${API_URL}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.ok) {
                    showToast(data.message || 'Password aggiornata', 'success');
                    setTimeout(() => {
                        window.location.href = 'sign-in.html';
                    }, 1200);
                } else {
                    if (response.status === 422 && data.errors) {
                        if (data.errors.email) showFieldError('resetEmail', data.errors.email[0]);
                        if (data.errors.password) showFieldError('resetPassword', data.errors.password[0]);
                    } else {
                        showToast(data.message || 'Errore durante il reset', 'error');
                    }
                }
            } catch (error) {
                console.error('Errore:', error);
                showToast('Errore di connessione', 'error');
            } finally {
                setButtonLoading(submitBtn, false);
            }
        });
    }
    
    // Toggle password visibility
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetSelector = this.getAttribute('data-target');
            const input = targetSelector
                ? document.querySelector(targetSelector)
                : this.closest('.input-group')?.querySelector('input');
            const icon = this.querySelector('i');

            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBars = document.querySelectorAll('.strength-bar');
            const strengthText = document.getElementById('passwordStrengthText');
            
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBars.forEach((bar, index) => {
                if (index < strength) {
                    bar.classList.add('active');
                } else {
                    bar.classList.remove('active');
                }
            });
            
            const strengthLabels = ['Molto Debole', 'Debole', 'Media', 'Forte', 'Molto Forte'];
            if (strengthText) {
                strengthText.textContent = `Forza password: ${strengthLabels[strength] || 'Molto Debole'}`;
            }
        });
    }
});

// Funzione logout
async function logout() {
    const token = localStorage.getItem('token');
    
    if (!token) {
        window.location.href = 'sign-in.html';
        return;
    }
    
    try {
        await fetch(`${API_URL}/logout`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        showToast('Logout effettuato con successo', 'success');
    } catch (error) {
        console.error('Errore logout:', error);
    } finally {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        
        setTimeout(() => {
            window.location.href = 'sign-in.html';
        }, 1000);
    }
}
