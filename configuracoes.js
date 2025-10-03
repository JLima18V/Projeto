// Navegação entre abas
document.addEventListener('DOMContentLoaded', function() {
    // Configuração das abas
    const menuItems = document.querySelectorAll('.menu-item');
    const tabContents = document.querySelectorAll('.tab-content');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove classe active de todos os itens e conteúdos
            menuItems.forEach(i => i.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Adiciona classe active ao item clicado
            this.classList.add('active');
            
            // Mostra o conteúdo correspondente
            const tabId = this.getAttribute('data-tab');
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        });
    });
    
    // Upload de foto de perfil
    const fotoInput = document.getElementById('foto_perfil');
    const fotoPreview = document.querySelector('.foto-perfil-grande');
    
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Contador de caracteres da biografia
    const bioTextarea = document.getElementById('bio');
    const contador = document.querySelector('.contador-caracteres');
    
    if (bioTextarea && contador) {
        bioTextarea.addEventListener('input', function() {
            const caracteres = this.value.length;
            contador.textContent = `${caracteres}/160`;
            
            if (caracteres > 160) {
                contador.style.color = '#dc3545';
            } else if (caracteres > 140) {
                contador.style.color = '#ffc107';
            } else {
                contador.style.color = '#6c757d';
            }
        });
        
        // Inicializar contador
        bioTextarea.dispatchEvent(new Event('input'));
    }
    
    // Validação de força da senha
    const novaSenhaInput = document.getElementById('nova_senha');
    const barraSenha = document.getElementById('barra-senha');
    const textoSenha = document.getElementById('texto-senha');
    const confirmarSenhaInput = document.getElementById('confirmar_senha');
    const erroSenha = document.getElementById('erro-senha');
    
    if (novaSenhaInput && barraSenha && textoSenha) {
        novaSenhaInput.addEventListener('input', function() {
            const senha = this.value;
            const forca = calcularForcaSenha(senha);
            
            barraSenha.style.width = forca.percentual + '%';
            barraSenha.style.background = forca.cor;
            textoSenha.textContent = forca.texto;
            textoSenha.style.color = forca.cor;
        });
    }
    
    if (confirmarSenhaInput && erroSenha) {
        confirmarSenhaInput.addEventListener('input', function() {
            const senha = novaSenhaInput.value;
            const confirmacao = this.value;
            
            if (confirmacao && senha !== confirmacao) {
                erroSenha.style.display = 'block';
                this.style.borderColor = '#dc3545';
            } else {
                erroSenha.style.display = 'none';
                this.style.borderColor = '#28a745';
            }
        });
    }
    
    // Tema e aparência
    const temaBotoes = document.querySelectorAll('.tema-botao');
    const fonteBotoes = document.querySelectorAll('.fonte-botao');
    
    temaBotoes.forEach(botao => {
        botao.addEventListener('click', function() {
            temaBotoes.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const tema = this.getAttribute('data-tema');
            aplicarTema(tema);
        });
    });
    
    fonteBotoes.forEach(botao => {
        botao.addEventListener('click', function() {
            fonteBotoes.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const fonte = this.getAttribute('data-fonte');
            aplicarFonte(fonte);
        });
    });
});

// Função para calcular força da senha
function calcularForcaSenha(senha) {
    let score = 0;
    
    if (!senha) {
        return { percentual: 0, cor: '#6c757d', texto: 'Força da senha' };
    }
    
    // Critérios de força
    if (senha.length >= 8) score++;
    if (senha.length >= 12) score++;
    if (/[A-Z]/.test(senha)) score++;
    if (/[a-z]/.test(senha)) score++;
    if (/[0-9]/.test(senha)) score++;
    if (/[^A-Za-z0-9]/.test(senha)) score++;
    
    const percentual = (score / 6) * 100;
    
    if (score <= 2) {
        return { percentual, cor: '#dc3545', texto: 'Fraca' };
    } else if (score <= 4) {
        return { percentual, cor: '#ffc107', texto: 'Média' };
    } else {
        return { percentual, cor: '#28a745', texto: 'Forte' };
    }
}

// Função para aplicar tema
function aplicarTema(tema) {
    document.body.className = '';
    document.body.classList.add(`tema-${tema}`);
    localStorage.setItem('tema', tema);
}

// Função para aplicar fonte
function aplicarFonte(fonte) {
    document.body.className = document.body.className.replace(/\bfonte-\S+/g, '');
    document.body.classList.add(`fonte-${fonte}`);
    localStorage.setItem('fonte', fonte);
}

// WhatsApp functions
function formatarWhatsApp(input) {
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length > 11) {
        valor = valor.substring(0, 11);
    }
    
    let valorFormatado = '';
    
    if (valor.length > 0) {
        valorFormatado = '(' + valor.substring(0, 2);
    }
    
    if (valor.length > 2) {
        if (valor.length <= 10) {
            valorFormatado += ') ' + valor.substring(2, 6);
            if (valor.length > 6) {
                valorFormatado += '-' + valor.substring(6, 10);
            }
        } else {
            valorFormatado += ') ' + valor.substring(2, 7);
            if (valor.length > 7) {
                valorFormatado += '-' + valor.substring(7, 11);
            }
        }
    } else {
        valorFormatado = '() -';
    }
    
    input.value = valorFormatado;
    validarCampoWhatsApp(valor);
}

function permitirApenasNumeros(event) {
    const teclasPermitidas = [
        'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
        'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
        'Home', 'End'
    ];
    
    if (teclasPermitidas.includes(event.key) || 
        (event.key >= '0' && event.key <= '9') ||
        (event.ctrlKey && (event.key === 'a' || event.key === 'c' || event.key === 'v' || event.key === 'x'))) {
        return true;
    }
    
    event.preventDefault();
    return false;
}

function validarCampoWhatsApp(valorNumerico) {
    const errorElement = document.getElementById('whatsapp-error');
    const inputElement = document.getElementById('whatsapp');
    
    if (!errorElement || !inputElement) return;
    
    if (valorNumerico.length === 10 || valorNumerico.length === 11 || valorNumerico.length === 0) {
        errorElement.style.display = 'none';
        inputElement.style.borderColor = '#28a745';
    } else {
        errorElement.style.display = 'block';
        inputElement.style.borderColor = '#dc3545';
    }
}

function removerFoto() {
    if (confirm('Tem certeza que deseja remover sua foto de perfil?')) {
        const fotoPreview = document.querySelector('.foto-perfil-grande');
        fotoPreview.src = 'imagens/icone-perfil.svg';
        
        // Aqui você pode adicionar uma chamada AJAX para remover a foto do servidor
        console.log('Foto removida');
    }
}

function encerrarOutrasSessoes() {
    if (confirm('Deseja encerrar todas as outras sessões?')) {
        // Aqui você pode adicionar uma chamada AJAX para encerrar sessões
        alert('Todas as outras sessões foram encerradas.');
    }
}

// Carregar preferências salvas
document.addEventListener('DOMContentLoaded', function() {
    const temaSalvo = localStorage.getItem('tema') || 'claro';
    const fonteSalva = localStorage.getItem('fonte') || 'medio';
    
    aplicarTema(temaSalvo);
    aplicarFonte(fonteSalva);
    
    // Ativar botões correspondentes
    document.querySelector(`[data-tema="${temaSalvo}"]`)?.classList.add('active');
    document.querySelector(`[data-fonte="${fonteSalva}"]`)?.classList.add('active');
});