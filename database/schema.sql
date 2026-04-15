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
DROP TABLE IF EXISTS `gruppe5`.`teilnehmen` ;

CREATE TABLE IF NOT EXISTS `gruppe5`.`teilnehmen` (
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
