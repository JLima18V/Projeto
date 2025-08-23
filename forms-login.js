// Alternar entre login e cadastro com animação
document.getElementById('showCadastro').onclick = function(e) {
    e.preventDefault();
    const loginForm = document.getElementById('loginForm');
    const cadastroForm = document.getElementById('cadastroForm');
    
    limparFormulario('loginForm'); // Limpa o formulário de login
    
    loginForm.classList.add('fade-out');
    setTimeout(() => {
        loginForm.style.display = 'none';
        loginForm.classList.remove('fade-out');
        cadastroForm.style.display = 'block';
    }, 300);
};

document.getElementById('showLogin').onclick = function(e) {
    e.preventDefault();
    const cadastroForm = document.getElementById('cadastroForm');
    const loginForm = document.getElementById('loginForm');
    
    limparFormulario('cadastroForm'); // Limpa o formulário de cadastro
    
    cadastroForm.classList.add('fade-out');
    setTimeout(() => {
        cadastroForm.style.display = 'none';
        cadastroForm.classList.remove('fade-out');
        loginForm.style.display = 'block';
    }, 300);
};

// Validação de senha e confirmação no submit
document.getElementById('cadastroForm').onsubmit = function() {
    const senha = document.getElementById('cadastro_senha').value;
    const confirmarSenha = document.getElementById('confirmar_senha').value;
    const erroSenha = document.getElementById('erro_senha');
    const erroTamanho = document.getElementById('erro_tamanho');
    let valido = true;

    if (senha.length < 8) {
        erroTamanho.style.display = 'block';
        erroTamanho.classList.add('show');
        valido = false;
    } else {
        erroTamanho.classList.remove('show');
        setTimeout(() => { erroTamanho.style.display = 'none'; }, 300);
    }

    if (senha !== confirmarSenha) {
        erroSenha.style.display = 'block';
        erroSenha.classList.add('show');
        valido = false;
    } else {
        erroSenha.classList.remove('show');
        setTimeout(() => { erroSenha.style.display = 'none'; }, 300);
    }

    return valido;
};

// Botões de mostrar/ocultar senha
document.getElementById('toggleLoginSenha').addEventListener('click', function() {
    const senhaInput = document.getElementById('login_senha');
    const eyeIcon = document.getElementById('login_eyeIcon');
    if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        eyeIcon.src = 'Imagens/Login-Conta/eye.svg';
        eyeIcon.alt = 'Ocultar senha';
    } else {
        senhaInput.type = 'password';
        eyeIcon.src = 'Imagens/Login-Conta/eye-slash.svg';
        eyeIcon.alt = 'Mostrar senha';
    }
});

document.getElementById('toggleCadastroSenha').addEventListener('click', function() {
    const senhaInput = document.getElementById('cadastro_senha');
    const eyeIcon = document.getElementById('cadastro_eyeIcon');
    if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        eyeIcon.src = 'Imagens/Login-Conta/eye.svg';
        eyeIcon.alt = 'Ocultar senha';
    } else {
        senhaInput.type = 'password';
        eyeIcon.src = 'Imagens/Login-Conta/eye-slash.svg';
        eyeIcon.alt = 'Mostrar senha';
    }
});

document.getElementById('toggleConfirmarSenha').addEventListener('click', function() {
    const senhaInput = document.getElementById('confirmar_senha');
    const eyeIcon = document.getElementById('confirmar_eyeIcon');
    if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        eyeIcon.src = 'Imagens/Login-Conta/eye.svg';
        eyeIcon.alt = 'Ocultar senha';
    } else {
        senhaInput.type = 'password';
        eyeIcon.src = 'Imagens/Login-Conta/eye-slash.svg';
        eyeIcon.alt = 'Mostrar senha';
    }
});

// Sistema de validação principal
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('cadastro_email');
    const nomeInput = document.getElementById('nome');
    const usuarioInput = document.getElementById('nome_usuario');
    const errorDiv = document.getElementById('cadastro-error-message');
    const errorList = document.getElementById('cadastro-error-list');

    // Auto-hide APENAS para erro de login (não para erros de cadastro)
    const loginErrorMessage = document.querySelector('#loginForm .login-error-message');
    if (loginErrorMessage) {
        setTimeout(() => {
            loginErrorMessage.classList.add('fade-out');
            setTimeout(() => {
                loginErrorMessage.style.display = 'none';
            }, 500);
        }, 10000);
    }

    // Função para remover acentos e caracteres especiais
    function removerAcentos(str) {
        return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    // Função para validar email institucional
    function validarEmail(email) {
        return /^[a-zA-Z]+\.[0-9]{13}@aluno\.etejk\.faetec\.rj\.gov\.br$/.test(email);
    }

    // Função para extrair nome do email
    function extrairNomeDoEmail(email) {
        const match = email.match(/^([a-zA-Z]+)\.[0-9]{13}@aluno\.etejk\.faetec\.rj\.gov\.br$/);
        return match ? match[1].toLowerCase() : "";
    }

    // Função para validar nome de usuário
    function validarNomeUsuario(usuario) {
        return /^[a-zA-Z0-9_]+$/.test(usuario);
    }

    // Função para mostrar erros com animação
    function mostrarErros(erros) {
        errorList.innerHTML = '';
        
        if (erros.length === 0) {
            esconderErros();
            return;
        }

        erros.forEach((erro, index) => {
            const li = document.createElement('li');
            li.textContent = erro;
            li.style.animationDelay = `${index * 0.1}s`;
            errorList.appendChild(li);
        });

        errorDiv.style.display = 'block';
        // Pequeno delay para garantir que o display:block seja aplicado antes da animação
        setTimeout(() => {
            errorDiv.classList.add('show');
        }, 10);
    }

    // Função para esconder erros com animação
    function esconderErros() {
        if (errorDiv.classList.contains('show')) {
            errorDiv.classList.remove('show');
            errorDiv.classList.add('fade-out');
            
            setTimeout(() => {
                errorDiv.style.display = 'none';
                errorDiv.classList.remove('fade-out');
                errorList.innerHTML = '';
            }, 500);
        }
    }

    // Função principal de validação
    function validarFormulario() {
        const email = emailInput.value.trim();
        const nome = nomeInput.value.trim();
        const usuario = usuarioInput.value.trim();
        const erros = [];

        // Validar email (apenas se não estiver vazio)
        if (email && !validarEmail(email)) {
            erros.push('Email deve seguir o formato: nome.matricula@aluno.etejk.faetec.rj.gov.br');
        }

        // Validar se nome corresponde ao email (apenas se ambos não estiverem vazios)
        if (email && nome) {
            const nomeDoEmail = extrairNomeDoEmail(email);
            const nomeSemAcentos = removerAcentos(nome.toLowerCase());
            
            if (nomeDoEmail && nomeSemAcentos !== nomeDoEmail) {
                erros.push('O nome deve ser igual ao nome presente no email');
            }
        }

        // Validar nome de usuário (apenas se não estiver vazio)
        if (usuario && !validarNomeUsuario(usuario)) {
            erros.push('Nome de usuário pode conter apenas letras, números e "_"');
        }

        // Mostrar ou esconder erros apenas se houver mudanças
        const errosAtuais = Array.from(errorList.children).map(li => li.textContent);
        const errosIguais = erros.length === errosAtuais.length && 
                           erros.every((erro, index) => erro === errosAtuais[index]);

        if (!errosIguais) {
            if (erros.length > 0) {
                mostrarErros(erros);
            } else {
                esconderErros();
            }
        }

        return erros;
    }

    // Event listeners para validação em tempo real
    emailInput.addEventListener('input', function() {
        clearTimeout(this.validationTimeout);
        this.validationTimeout = setTimeout(validarFormulario, 300);
    });

    nomeInput.addEventListener('input', function() {
        clearTimeout(this.validationTimeout);
        this.validationTimeout = setTimeout(validarFormulario, 300);
    });

    usuarioInput.addEventListener('input', function() {
        clearTimeout(this.validationTimeout);
        this.validationTimeout = setTimeout(validarFormulario, 300);
    });

    // Removido os event listeners de blur para evitar o piscar
    // Manter apenas a validação no input com debounce

    // Impedir submit se houver erro de nome de usuário
    document.getElementById('cadastroForm').addEventListener('submit', function(e) {
        const erros = validarFormulario();
        if (erros.length > 0) {
            e.preventDefault();
        }
    });

    // Função para aplicar efeito de erro
    function aplicarEfeitoErro(inputBox) {
        const input = inputBox.querySelector('input');
        const icon = inputBox.querySelector('.input-icon');
        const eyeBtn = inputBox.querySelector('.eye-btn');

        // Adiciona classes de erro
        input.classList.add('error-shake');
        if (icon) icon.classList.add('error-shake');
        if (eyeBtn) eyeBtn.classList.add('error-shake');

        // Remove classes após a animação
        setTimeout(() => {
            input.classList.remove('error-shake');
            if (icon) icon.classList.remove('error-shake');
            if (eyeBtn) eyeBtn.classList.remove('error-shake');
        }, 300);
    }

    // Função para bloquear espaços com efeito
    function bloquearEspacos(input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === ' ') {
                e.preventDefault();
                const inputBox = this.closest('.input-box');
                aplicarEfeitoErro(inputBox);
            }
        });
        
        input.addEventListener('input', function() {
            if (this.value.includes(' ')) {
                this.value = this.value.replace(/\s/g, '');
                const inputBox = this.closest('.input-box');
                aplicarEfeitoErro(inputBox);
            }
        });
    }

    // Aplicar para todos os campos de texto do cadastro
    const inputsParaValidar = [
        'nome', 
        'sobrenome', 
        'nome_usuario', 
        'cadastro_email', 
        'cadastro_senha', 
        'confirmar_senha'
    ];

    inputsParaValidar.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            bloquearEspacos(input);
        }
    });

    // Limpar erros quando o formulário for resetado ou alternado
    const limparErrosQuandoAlternar = function() {
        esconderErros();
    };

    // Adicionar listener aos links de alternância
    document.getElementById('showCadastro').addEventListener('click', limparErrosQuandoAlternar);
    document.getElementById('showLogin').addEventListener('click', limparErrosQuandoAlternar);

    // Sistema de Upload de Foto de Perfil
    const fotoInput = document.getElementById('foto_perfil');
    const fotoPreview = document.getElementById('fotoPerfilImg');
    const uploadArea = document.querySelector('.upload-area');
    const removeFotoBtn = document.getElementById('removeFoto');
    const defaultImage = 'Imagens/Login-Conta/person-circle.svg';

    // Preview da foto
    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validações
            if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/i)) {
                alert('Por favor, selecione uma imagem válida (JPG, PNG, GIF ou WebP)');
                this.value = '';
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) { // 5MB
                alert('A imagem deve ter no máximo 5MB');
                this.value = '';
                return;
            }

            // Preview
            const reader = new FileReader();
            reader.onload = function(ev) {
                fotoPreview.src = ev.target.result;
                removeFotoBtn.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        }
    });

    // Remover foto
    removeFotoBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Impede que o clique propague para a área de upload
        fotoInput.value = ''; // Limpa o input file
        fotoPreview.src = defaultImage;
        this.style.display = 'none';
    });

    // Atualizar o suporte para drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const file = e.dataTransfer.files[0];
        if (file) {
            fotoInput.files = e.dataTransfer.files;
            const event = new Event('change');
            fotoInput.dispatchEvent(event);
        }
    });

    // Clique na área de preview também abre o seletor de arquivo
    document.getElementById('fotoPerfilPreview').addEventListener('click', function(e) {
        e.preventDefault();
        fotoInput.click();
    });
});

function limparFormulario(formId) {
    const form = document.getElementById(formId);
    form.reset();
    // Limpa mensagens de erro
    const errorDiv = document.getElementById('cadastro-error-message');
    const erroSenha = document.getElementById('erro_senha');
    const erroTamanho = document.getElementById('erro_tamanho');
    
    errorDiv.classList.remove('show');
    errorDiv.style.display = 'none';
    erroSenha.style.display = 'none';
    erroTamanho.style.display = 'none';
}