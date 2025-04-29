-- Primeiro, adiciona uma coluna temporária
ALTER TABLE usuario ADD COLUMN tipo_novo BOOLEAN;

-- Atualiza os valores na coluna temporária
UPDATE usuario SET tipo_novo = CASE 
    WHEN tipo = 'admin' THEN TRUE 
    ELSE FALSE 
END;

-- Remove a coluna antiga
ALTER TABLE usuario DROP COLUMN tipo;

-- Renomeia a nova coluna
ALTER TABLE usuario RENAME COLUMN tipo_novo TO tipo;

-- Adiciona a constraint NOT NULL e o valor padrão
ALTER TABLE usuario ALTER COLUMN tipo SET NOT NULL;
ALTER TABLE usuario ALTER COLUMN tipo SET DEFAULT FALSE; 