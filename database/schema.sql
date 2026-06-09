-- -----------------------------------------------------
-- Table `gruppe5`.`Teamchef`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gruppe5`.`Teamchef` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`Teamchef` (
  `TeamchefLoginName` VARCHAR(50) NOT NULL,
  `Kennwort` VARCHAR(255) NULL,
  `Vorname` VARCHAR(45) NULL,
  `Nachname` VARCHAR(45) NULL,
  PRIMARY KEY (`TeamchefLoginName`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `gruppe5`.`Veranstalter`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gruppe5`.`Veranstalter` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`Veranstalter` (
  `VeranstalterLoginName` VARCHAR(50) NOT NULL,
  `Kennwort` VARCHAR(255) NULL,
  PRIMARY KEY (`VeranstalterLoginName`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `gruppe5`.`Trainingsziele`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gruppe5`.`Trainingsziele` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`Trainingsziele` (
  `Trainingsziel` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`Trainingsziel`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `gruppe5`.`Team`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gruppe5`.`Team` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`Team` (
  `Teamname` VARCHAR(50) NOT NULL,
  `TeamchefLoginName` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`Teamname`),
  INDEX `fk_Team_Teamchef_idx` (`TeamchefLoginName` ASC) VISIBLE,
  CONSTRAINT `fk_Team_Teamchef`
    FOREIGN KEY (`TeamchefLoginName`)
    REFERENCES `gruppe5`.`Teamchef` (`TeamchefLoginName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `gruppe5`.`Fahrer`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gruppe5`.`Fahrer` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`Fahrer` (
  `Mitarbeiter-ID` INT NOT NULL,
  `Vorname` VARCHAR(45) NULL,
  `Nachname` VARCHAR(45) NULL,
  `Straße` VARCHAR(45) NULL,
  `Hausnummer` VARCHAR(45) NULL,
  `Telefonnummer` VARCHAR(45) NULL,
  `PLZ` VARCHAR(5) NULL,
  `Ort` VARCHAR(45) NULL,
  `Teamname` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`Mitarbeiter-ID`, `Teamname`),
  INDEX `fk_Fahrer_Team1_idx` (`Teamname` ASC) VISIBLE,
  CONSTRAINT `fk_Fahrer_Team1`
    FOREIGN KEY (`Teamname`)
    REFERENCES `gruppe5`.`Team` (`Teamname`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `gruppe5`.`Rennen`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gruppe5`.`Rennen` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`Rennen` (
  `RennId` INT NOT NULL AUTO_INCREMENT,
  `Datum` DATE NULL,
  `PLZ` VARCHAR(5) NULL,
  `Ort` VARCHAR(45) NULL,
  `Kilometer` DECIMAL(10,2) NULL,
  `Steigung` DECIMAL(10,2) NULL,
  `Hoehenmeter` DECIMAL(10,2) NULL,
  `VeranstalterLoginName` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`RennId`),
  INDEX `fk_Rennen_Veranstalter1_idx` (`VeranstalterLoginName` ASC) VISIBLE,
  CONSTRAINT `fk_Rennen_Veranstalter1`
    FOREIGN KEY (`VeranstalterLoginName`)
    REFERENCES `gruppe5`.`Veranstalter` (`VeranstalterLoginName`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `gruppe5`.`teilnehmen`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gruppe5`.`Teilnahme` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`Teilnahme` (
  `MitarbeiterId` INT NOT NULL,
  `Teamname` VARCHAR(50) NOT NULL,
  `RennId` INT NOT NULL,
  `Startnummer` INT NULL,
  `Platzierung` INT NULL,
  `Zeit` TIME NULL,
  `VeranstalterPraemie` DECIMAL(10,2) NULL,
  `TeamPraemie` DECIMAL(10,2) NULL,
  PRIMARY KEY (`MitarbeiterId`, `Teamname`, `RennId`),
  INDEX `fk_Fahrer_has_Rennen_Rennen1_idx` (`RennId` ASC) VISIBLE,
  INDEX `fk_Fahrer_has_Rennen_Fahrer1_idx` (`MitarbeiterId` ASC, `Teamname` ASC) VISIBLE,
  CONSTRAINT `fk_Fahrer_has_Rennen_Fahrer1`
    FOREIGN KEY (`MitarbeiterId` , `Teamname`)
    REFERENCES `gruppe5`.`Fahrer` (`Mitarbeiter-ID` , `Teamname`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Fahrer_has_Rennen_Rennen1`
    FOREIGN KEY (`RennId`)
    REFERENCES `gruppe5`.`Rennen` (`RennId`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `gruppe5`.`Training`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gruppe5`.`Training` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`Training` (
  `Datum` DATE NOT NULL,
  `Kilometer` DECIMAL(10,2) NULL,
  `Mitarbeiter-ID` INT NOT NULL,
  `Teamname` VARCHAR(50) NOT NULL,
  `Trainingsziel` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`Datum`, `Mitarbeiter-ID`, `Teamname`),
  INDEX `fk_Training_Fahrer1_idx` (`Mitarbeiter-ID` ASC, `Teamname` ASC) VISIBLE,
  INDEX `fk_Training_Trainingsziele1_idx` (`Trainingsziel` ASC) VISIBLE,
  CONSTRAINT `fk_Training_Fahrer1`
    FOREIGN KEY (`Mitarbeiter-ID` , `Teamname`)
    REFERENCES `gruppe5`.`Fahrer` (`Mitarbeiter-ID` , `Teamname`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Training_Trainingsziele1`
    FOREIGN KEY (`Trainingsziel`)
    REFERENCES `gruppe5`.`Trainingsziele` (`Trainingsziel`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


-- -----------------------------------------------------
-- Stored Procedure `gruppe5`.`fahrer_anlegen`
-- Nicolas Biercher
-- Legt einen neuen Fahrer mit teamspezifisch vergeben Mitarbeiter-ID an.
-- Gibt erfolg (1/0) und meldung als Ergebniszeile zurück.
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `gruppe5`.`fahrer_anlegen`;

DELIMITER //

CREATE PROCEDURE `gruppe5`.`fahrer_anlegen`(
    IN p_vorname      VARCHAR(45),
    IN p_nachname     VARCHAR(45),
    IN p_strasse      VARCHAR(45),
    IN p_hausnummer   VARCHAR(45),
    IN p_telefonnummer VARCHAR(45),
    IN p_plz          VARCHAR(5),
    IN p_ort          VARCHAR(45),
    IN p_teamname     VARCHAR(50)
)
BEGIN
    DECLARE v_erfolg INT DEFAULT 1;
    DECLARE v_meldung VARCHAR(255) DEFAULT '';
    DECLARE v_anzahl INT DEFAULT 0;
    DECLARE v_neue_mitarbeiter_id INT DEFAULT 1;

    IF p_teamname IS NULL OR TRIM(p_teamname) = '' THEN
        SET v_erfolg = 0;
        SET v_meldung = 'Teamname darf nicht leer sein';
    END IF;

    IF v_erfolg = 1 AND (p_vorname IS NULL OR TRIM(p_vorname) = '') THEN
        SET v_erfolg = 0;
        SET v_meldung = 'Vorname darf nicht leer sein';
    END IF;

    IF v_erfolg = 1 AND (p_nachname IS NULL OR TRIM(p_nachname) = '') THEN
        SET v_erfolg = 0;
        SET v_meldung = 'Nachname darf nicht leer sein';
    END IF;

    IF v_erfolg = 1 AND (p_strasse IS NULL OR TRIM(p_strasse) = '') THEN
        SET v_erfolg = 0;
        SET v_meldung = 'Straße darf nicht leer sein';
    END IF;

    IF v_erfolg = 1 AND (p_hausnummer IS NULL OR TRIM(p_hausnummer) = '') THEN
        SET v_erfolg = 0;
        SET v_meldung = 'Hausnummer darf nicht leer sein';
    END IF;

    IF v_erfolg = 1 AND (p_telefonnummer IS NULL OR TRIM(p_telefonnummer) = '') THEN
        SET v_erfolg = 0;
        SET v_meldung = 'Telefonnummer darf nicht leer sein';
    END IF;

    IF v_erfolg = 1 AND (p_plz IS NULL OR p_plz NOT REGEXP '^[0-9]{5}$') THEN
        SET v_erfolg = 0;
        SET v_meldung = 'PLZ muss genau 5 Ziffern haben';
    END IF;

    IF v_erfolg = 1 AND (p_ort IS NULL OR TRIM(p_ort) = '') THEN
        SET v_erfolg = 0;
        SET v_meldung = 'Ort darf nicht leer sein';
    END IF;

    IF v_erfolg = 1 THEN
        SELECT COUNT(*) INTO v_anzahl
        FROM Team
        WHERE Teamname = p_teamname;

        IF v_anzahl = 0 THEN
            SET v_erfolg = 0;
            SET v_meldung = 'Team existiert nicht';
        END IF;
    END IF;

    IF v_erfolg = 1 THEN
        SELECT COALESCE(MAX(`Mitarbeiter-ID`), 0) + 1
        INTO v_neue_mitarbeiter_id
        FROM Fahrer
        WHERE Teamname = p_teamname
        FOR UPDATE;
    END IF;

    IF v_erfolg = 1 THEN
        INSERT INTO Fahrer
        (`Mitarbeiter-ID`, Vorname, Nachname, `Straße`, Hausnummer, Telefonnummer, PLZ, Ort, Teamname)
        VALUES
        (v_neue_mitarbeiter_id, p_vorname, p_nachname, p_strasse, p_hausnummer, p_telefonnummer, p_plz, p_ort, p_teamname);

        SET v_meldung = CONCAT('Fahrer erfolgreich angelegt mit ID ', v_neue_mitarbeiter_id);
    END IF;

    SELECT v_erfolg AS erfolg, v_meldung AS meldung;

END //

DELIMITER ;
