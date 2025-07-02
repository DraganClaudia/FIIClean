class SimpleAuthManager {
            constructor() {
                this.token = localStorage.getItem('auth_token');
                this.user = JSON.parse(localStorage.getItem('user_data') || 'null');
            }
            
            isAuthenticated() {
                return this.token && this.user;
            }
            
            setAuth(token, user) {
                this.token = token;
                this.user = user;
                localStorage.setItem('auth_token', token);
                localStorage.setItem('user_data', JSON.stringify(user));
            }
            
            clearAuth() {
                this.token = null;
                this.user = null;
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
            }
            
            async login(username, password) {
                const response = await fetch('../api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.setAuth(data.token, data.user);
                    return { success: true, user: data.user };
                }
                
                return { success: false, error: data.error };
            }
            
            async register(userData) {
                const response = await fetch('../api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(userData)
                });
                
                const data = await response.json();
                
                if (data.success && data.token) {
                    this.setAuth(data.token, { id: data.user_id, role: 'client' });
                }
                
                return data;
            }
            
            redirectToDashboard() {
                if (!this.isAuthenticated()) {
                    return;
                }
                
                const role = this.user.role;
                const dashboards = {
                    'admin': 'admin.html',
                    'manager': 'manager.html',
                    'client': 'client.html',
                    'worker_transport': 'transport.html',
                    'worker_cleaner': 'cleaner.html'
                };
                
                window.location.href = dashboards[role] || 'dashboard.html';
            }
        }
        
        const auth = new SimpleAuthManager();
        
        document.addEventListener('DOMContentLoaded', function() {
            if (auth.isAuthenticated()) {
                auth.redirectToDashboard();
                return;
            }
            
            document.getElementById('login-form').addEventListener('submit', handleLogin);
            document.getElementById('register-form').addEventListener('submit', handleRegister);
        });
        
        function showTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(tabName + '-tab').classList.add('active');
        }
        
        async function handleLogin(e) {
            e.preventDefault();
            
            const username = document.getElementById('login-username').value;
            const password = document.getElementById('login-password').value;
            
            if (!username || !password) {
                showMessage('Completați toate câmpurile', 'error');
                return;
            }
            
            try {
                const result = await auth.login(username, password);
                
                if (result.success) {
                    showMessage(`Bun venit, ${result.user.first_name}!`, 'success');
                    setTimeout(() => auth.redirectToDashboard(), 1000);
                } else {
                    showMessage(result.error, 'error');
                }
            } catch (error) {
                showMessage('Eroare de conexiune', 'error');
            }
        }
        
        async function handleRegister(e) {
            e.preventDefault();
            
            const formData = {
                first_name: document.getElementById('register-first-name').value,
                last_name: document.getElementById('register-last-name').value,
                username: document.getElementById('register-username').value,
                email: document.getElementById('register-email').value,
                phone: document.getElementById('register-phone').value,
                password: document.getElementById('register-password').value
            };
            
            const confirmPassword = document.getElementById('register-confirm').value;
            
            if (!formData.first_name || !formData.last_name || !formData.username || 
                !formData.email || !formData.password) {
                showMessage('Completați toate câmpurile obligatorii', 'error');
                return;
            }
            
            if (formData.password !== confirmPassword) {
                showMessage('Parolele nu se potrivesc', 'error');
                return;
            }
            
            try {
                const result = await auth.register(formData);
                
                if (result.success) {
                    showMessage('Cont creat cu succes!', 'success');
                    setTimeout(() => auth.redirectToDashboard(), 1000);
                } else {
                    showMessage(result.error, 'error');
                }
            } catch (error) {
                showMessage('Eroare de conexiune', 'error');
            }
        }
        
        function quickLogin(username, password) {
            document.getElementById('login-username').value = username;
            document.getElementById('login-password').value = password;
            handleLogin({ preventDefault: () => {} });
        }
        
        function showMessage(text, type) {
            const messageEl = document.getElementById('message');
            messageEl.textContent = text;
            messageEl.className = `message ${type}`;
            messageEl.style.display = 'block';
            
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 5000);
        }
        
        window.auth = auth;