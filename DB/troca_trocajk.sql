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
  id_usuario INT,
  data_publicacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
  );
  
  CREATE TABLE lista_desejos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_livro INT NOT NULL,
    data_adicionado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Garantir que o mesmo livro não seja adicionado duas vezes pelo mesmo usuário
    UNIQUE KEY (id_usuario, id_livro),

    -- Chaves estrangeiras (opcional, mas recomendado se você já tem as tabelas)
    CONSTRAINT fk_usuario_desejo FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_livro_desejo FOREIGN KEY (id_livro) REFERENCES livros(id) ON DELETE CASCADE
);


ALTER TABLE livros
DROP FOREIGN KEY livros_ibfk_1;

ALTER TABLE livros
ADD CONSTRAINT livros_ibfk_1
FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
ON DELETE CASCADE;

select * from lista_desejos;	