// Configurazione API
var API_URL = window.API_URL || window.location.origin;
window.API_URL = API_URL;

// Controlla se l'utente è loggato
function checkAuth() {
    const token = localStorage.getItem('token');
    
    if (!token) {
        window.location.href = 'sign-in.html';
        return false;
    }
    
    return true;
}

// Toast notifications
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

    // PWA safe-area adjustment (iOS)
    if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
        toast.style.top = 'calc(20px + env(safe-area-inset-top))';
    }

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

// Bottone loading
function setButtonLoading(button, loading) {
    if (!button) return;
    if (loading) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Uscita...
        `;
        button.disabled = true;
    } else {
        button.innerHTML = button.dataset.originalText || button.innerHTML;
        button.disabled = false;
    }
}

// Logout con feedback UI
function handleLogoutClick(button) {
    const overlay = document.getElementById('pageLoadingOverlay');
    if (overlay) overlay.classList.add('active');
    setButtonLoading(button, true);
    showToast('Logout in corso...', 'success');
    setTimeout(() => logout(), 300);
}

// Carica dati utente
async function loadUserData() {
    const token = localStorage.getItem('token');
    
    if (!token) return null;
    
    try {
        const response = await fetch(`${API_URL}/api/user`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = 'sign-in.html';
            return null;
        }
        
        const data = await response.json();
        localStorage.setItem('user', JSON.stringify(data.user));
        return data.user;
    } catch (error) {
        console.error('Errore caricamento utente:', error);
        return null;
    }
}

// Carica servizi disponibili
async function loadServices() {
    try {
        const response = await fetch(`${API_URL}/api/services`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            console.error('Errore caricamento servizi');
            return [];
        }
        
        const data = await response.json();
        return data.services || [];
    } catch (error) {
        console.error('Errore:', error);
        return [];
    }
}

// Carica pets dell'utente
async function loadMyPets() {
    const token = localStorage.getItem('token');
    
    try {
        const response = await fetch(`${API_URL}/api/my-pets`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            console.error('Errore caricamento pets');
            return [];
        }
        
        const data = await response.json();
        return data.pets || [];
    } catch (error) {
        console.error('Errore:', error);
        return [];
    }
}

// Carica slot disponibili
async function loadAvailableSlots(serviceId, date) {
    const token = localStorage.getItem('token');
    
    try {
        const response = await fetch(`${API_URL}/api/available-slots?service_id=${serviceId}&date=${date}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            return { success: false, message: data.message, slots: [] };
        }
        
        return data;
    } catch (error) {
        console.error('Errore:', error);
        return { success: false, message: 'Errore di connessione', slots: [] };
    }
}

// Mostra servizi nella pagina
function displayServices(services) {
    const container = document.getElementById('servicesContainer');
    
    if (!container) return;
    
    if (services.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Nessun servizio disponibile al momento.</p>';
        return;
    }
    
    container.innerHTML = services.map(service => `
        <div class="offer-card d-block mb-3">
            <span class="offer-badge">€ ${service.price}</span>
            <div class="d-flex align-items-center">
                <div class="service-image-container">
                    ${service.photo_url
                        ? `<img src="${service.photo_url}" alt="${service.name}" class="service-image">` 
                        : `<div class="icon-50 d-flex align-items-center justify-content-center rounded-3 gradient-1">
                             <i class="bi bi-sparkles text-white fs-24"></i>
                           </div>`
                    }
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="fw-semibold text-primary-color">${service.name}</div>
                    <div class="text-muted small">${service.description || 'Servizio di toelettatura'}</div>
                    <div class="mt-1 text-accent-color fs-11">
                        <i class="bi bi-clock me-1"></i>${service.duration} minuti
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Crea prenotazione
async function createBooking(bookingData) {
    const token = localStorage.getItem('token');
    
    try {
        const response = await fetch(`${API_URL}/api/bookings`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(bookingData)
        });
        
        const data = await response.json();
        
        return { success: response.ok, data };
    } catch (error) {
        console.error('Errore:', error);
        return { success: false, data: { message: 'Errore di connessione' } };
    }
}

// Logout
async function logout() {
    const token = localStorage.getItem('token');
    
    if (token) {
        try {
            await fetch(`${API_URL}/api/logout`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
        } catch (error) {
            console.error('Errore logout:', error);
        }
    }
    
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = 'sign-in.html';
}
