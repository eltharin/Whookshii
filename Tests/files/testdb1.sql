-- --------------------------------------------------------
-- Hôte:                         D:\laragon\www\whookshiitest\Core\Tests\files\test.db
-- Version du serveur:           3.34.0
-- SE du serveur:                
-- HeidiSQL Version:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES  */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Listage de la structure de la table test. Marque
CREATE TABLE IF NOT EXISTS "marque" (
	"VOI_MA_MARQUE"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"VOI_MA_LIBELLE"	VARCHAR(50) DEFAULT NULL,
	"VOI_MA_PAYS"	INTEGER
);

-- Listage des données de la table test.Marque : -1 rows
/*!40000 ALTER TABLE "Marque" DISABLE KEYS */;
INSERT INTO "Marque" ("VOI_MA_MARQUE", "VOI_MA_LIBELLE", "VOI_MA_PAYS") VALUES
	(1, 'Honda', 1),
	(2, 'Renault', 2);
/*!40000 ALTER TABLE "Marque" ENABLE KEYS */;

-- Listage de la structure de la table test. Pays
CREATE TABLE IF NOT EXISTS "pays" (
	"VOI_PAY_PAYS"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"VOI_PAY_LIBELLE"	VARCHAR(100)
);

-- Listage des données de la table test.Pays : -1 rows
/*!40000 ALTER TABLE "Pays" DISABLE KEYS */;
INSERT INTO "Pays" ("VOI_PAY_PAYS", "VOI_PAY_LIBELLE") VALUES
	(1, 'JAPON'),
	(2, 'FRANCE');
/*!40000 ALTER TABLE "Pays" ENABLE KEYS */;

-- Listage de la structure de la table test. Voiture
CREATE TABLE IF NOT EXISTS "voiture" (
	"VOI_VOI_VOITURE" INTEGER PRIMARY KEY AUTOINCREMENT,
	"VOI_VOI_MARQUE" INTEGER NULL,
	"VOI_VOI_IMMATRICULATION" VARCHAR(50) NULL DEFAULT NULL
);

-- Listage des données de la table test.Voiture : -1 rows
/*!40000 ALTER TABLE "Voiture" DISABLE KEYS */;
INSERT INTO "Voiture" ("VOI_VOI_VOITURE", "VOI_VOI_MARQUE", "VOI_VOI_IMMATRICULATION") VALUES
	(1, 1, '123-AB-456'),
	(2, 1, '789-BB-456'),
	(3, 2, '858-FV-546');
/*!40000 ALTER TABLE "Voiture" ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
