CREATE DATABASE troca_trocajk;

USE troca_trocajk;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    nome varchar(40) not null,
    sobrenome varchar (50) not null,
    nome_usuario VARCHAR(50) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    instagram varchar(100) default null,
    whatsapp varchar(14) default null,
    token varchar(100) default null,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE livros (
   id INT NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(255),
  autor VARCHAR(255),
  genero VARCHAR(100),
  estado VARCHAR(50),
  imagens varchar (255),
  status ENUM('disponivel','indisponivel') DEFAULT 'disponivel',
  id_usuario INT,
  data_publicacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
  ON DELETE CASCADE
   );
 
  
  CREATE TABLE lista_desejos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_livro INT NOT NULL,
    data_adicionado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (id_usuario, id_livro),
    CONSTRAINT fk_usuario_desejo FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_livro_desejo FOREIGN KEY (id_livro) REFERENCES livros(id) ON DELETE CASCADE
);



CREATE TABLE trocas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitante INT NOT NULL,
    id_receptor INT NOT NULL,
    id_livro_solicitado INT NOT NULL,
    confirm_solicitante TINYINT(1) DEFAULT 0,
    confirm_receptor TINYINT(1) DEFAULT 0,
    status ENUM('pendente', 'aceita', 'recusada', 'concluída') DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_solicitante) REFERENCES usuarios(id),
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id),
    FOREIGN KEY (id_livro_solicitado) REFERENCES livros(id)
);

CREATE TABLE trocas_livros_oferecidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_troca INT NOT NULL,
    id_livro_oferecido INT NOT NULL,
    FOREIGN KEY (id_troca) REFERENCES trocas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_livro_oferecido) REFERENCES livros(id)
);