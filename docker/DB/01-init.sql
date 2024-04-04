-- Créer les tables
CREATE TABLE beerlover (
    beerlover_id SERIAL PRIMARY KEY,
    beerlover_name VARCHAR(50),
    beerlover_mail VARCHAR(50),
    beerlover_password VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE brewery (
    brewery_id SERIAL PRIMARY KEY,
    brewery_name VARCHAR(50),
    brewery_country VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE beer (
   beer_id SERIAL PRIMARY KEY,
   beer_name VARCHAR(50),
   beer_description VARCHAR(2000),
   beer_abv FLOAT,
   brewery_id INT,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (brewery_id) REFERENCES brewery(brewery_id) 
);

CREATE TABLE review (
    review_id SERIAL PRIMARY KEY,
    review_note INT NOT NULL,
    review_comment VARCHAR(500),
    beer_id INT,
    beerlover_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (beer_id) REFERENCES beer(beer_id),
    FOREIGN KEY (beerlover_id) REFERENCES beerlover(beerlover_id)
);

CREATE TABLE photo (
   photo_id SERIAL PRIMARY KEY,
   photo_url VARCHAR(200),
   photo_alt VARCHAR(100),
   beer_id INT, 
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (beer_id) REFERENCES beer(beer_id)
);

CREATE TABLE ingredient (
  ingredient_id SERIAL PRIMARY KEY, 
  ingredient_name VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

CREATE TABLE favorite (
  beerlover_id INT, 
  beer_id INT, 
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(beerlover_id, beer_id), 
  FOREIGN KEY(beerlover_id) REFERENCES beerlover(beerlover_id), 
  FOREIGN KEY(beer_Id) REFERENCES beer(beer_id)
); 

CREATE TABLE category (
  category_id SERIAL PRIMARY KEY,
  category_name VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

CREATE TABLE category_beer (
  category_id INT,  
  beer_id INT, 
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    
  FOREIGN KEY(category_id) REFERENCES category(category_id),   
  FOREIGN KEY(beer_id) REFERENCES beer(beer_id)
);

CREATE TABLE beer_ingredient (
  beer_id INT, 
  ingredient_id INT, 
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(beer_id, ingredient_id),  
  FOREIGN KEY(beer_id) REFERENCES beer(beer_id),  
  FOREIGN KEY(ingredient_id) REFERENCES ingredient(ingredient_id)
); 
  
-- Trigger pour mettre a jour update_at automatiquement
CREATE OR REPLACE FUNCTION update_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Création des triggers pour chaque table
CREATE TRIGGER beerlover_update_trigger
BEFORE UPDATE ON beerlover
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER brewery_update_trigger
BEFORE UPDATE ON brewery
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER beer_update_trigger
BEFORE UPDATE ON beer
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER review_update_trigger
BEFORE UPDATE ON review
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER photo_update_trigger
BEFORE UPDATE ON photo
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER ingredient_update_trigger
BEFORE UPDATE ON ingredient
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER category_update_trigger
BEFORE UPDATE ON category
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER category_beer_update_trigger
BEFORE UPDATE ON category_beer
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER beer_ingredient_update_trigger
BEFORE UPDATE ON beer_ingredient
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();
