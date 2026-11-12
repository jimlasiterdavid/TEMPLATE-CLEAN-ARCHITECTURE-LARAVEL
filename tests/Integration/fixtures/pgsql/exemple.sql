-- Fonctions de TEST uniquement (les vraies procédures sont fournies par l'équipe base de données).

DROP TABLE IF EXISTS exemples CASCADE;

CREATE TABLE exemples (
    id SERIAL PRIMARY KEY,
    libelle VARCHAR(255) NOT NULL UNIQUE,
    montant NUMERIC(12, 2) NOT NULL
);

CREATE OR REPLACE FUNCTION ps_exemple_liste(p_page INT, p_par_page INT)
RETURNS TABLE (id INT, libelle VARCHAR, montant NUMERIC)
LANGUAGE sql STABLE AS $$
    SELECT e.id, e.libelle, e.montant
    FROM exemples e
    ORDER BY e.id
    LIMIT p_par_page OFFSET (p_page - 1) * p_par_page;
$$;

CREATE OR REPLACE FUNCTION ps_exemple_detail(p_id INT)
RETURNS TABLE (id INT, libelle VARCHAR, montant NUMERIC)
LANGUAGE sql STABLE AS $$
    SELECT e.id, e.libelle, e.montant FROM exemples e WHERE e.id = p_id;
$$;

CREATE OR REPLACE FUNCTION ps_exemple_creer(p_libelle VARCHAR, p_montant NUMERIC)
RETURNS TABLE (id INT, libelle VARCHAR, montant NUMERIC)
LANGUAGE plpgsql AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM exemples e WHERE e.libelle = p_libelle) THEN
        RAISE EXCEPTION 'Ce libellé existe déjà.';
    END IF;

    RETURN QUERY
        INSERT INTO exemples AS e (libelle, montant)
        VALUES (p_libelle, p_montant)
        RETURNING e.id, e.libelle, e.montant;
END;
$$;

-- Vérifie le typage des paramètres (booléen, null) et les paramètres nommés.
CREATE OR REPLACE FUNCTION ps_test_types(p_actif BOOLEAN, p_note TEXT DEFAULT NULL)
RETURNS TABLE (actif BOOLEAN, note_absente BOOLEAN)
LANGUAGE sql IMMUTABLE AS $$
    SELECT p_actif, p_note IS NULL;
$$;
