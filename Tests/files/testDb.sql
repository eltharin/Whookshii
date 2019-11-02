
--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `CON_CAT_CATEGORIE` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `CON_CAT_COMPTE` int(11) NOT NULL,
  `CON_CAT_LIBELLE` varchar(50) NOT NULL,
  `CON_CAT_IMAGE` varchar(255) NOT NULL
);

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`CON_CAT_CATEGORIE`, `CON_CAT_COMPTE`, `CON_CAT_LIBELLE`, `CON_CAT_IMAGE`) VALUES
(1, 1, 'Viande', 'viande.png'),
(2, 1, 'Plat cuisinés', 'plats.png');

-- --------------------------------------------------------

--
-- Structure de la table `compte`
--

CREATE TABLE `compte` (
  `CON_COM_COMPTE` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `CON_COM_LIBELLE` varchar(50) NOT NULL
);

--
-- Déchargement des données de la table `compte`
--

INSERT INTO `compte` (`CON_COM_COMPTE`, `CON_COM_LIBELLE`) VALUES
(1, 'Roman'),
(2, 'Cécile');

-- --------------------------------------------------------

--
-- Structure de la table `emplacement`
--

CREATE TABLE `emplacement` (
  `CON_EMP_EMPLACEMENT` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `CON_EMP_COMPTE` int(11) NOT NULL,
  `CON_EMP_LIBELLE` varchar(100) NOT NULL
);

--
-- Déchargement des données de la table `emplacement`
--

INSERT INTO `emplacement` (`CON_EMP_EMPLACEMENT`, `CON_EMP_COMPTE`, `CON_EMP_LIBELLE`) VALUES
(1, 1, 'Coffre - Bac haut gauche'),
(2, 1, 'Coffre - Bac bas gauche');

-- --------------------------------------------------------

--
-- Structure de la table `item`
--

CREATE TABLE `item` (
  `CON_I_ITEM` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `CON_I_PRODUIT` int(11) NOT NULL,
  `CON_I_EMPLACEMENT` int(11) NOT NULL,
  `CON_I_DATEAJOUT` varchar(20) NOT NULL,
  `CON_I_DATESUPPR` varchar(20) NOT NULL
);

--
-- Déchargement des données de la table `item`
--

INSERT INTO `item` (`CON_I_ITEM`, `CON_I_PRODUIT`, `CON_I_EMPLACEMENT`, `CON_I_DATEAJOUT`, `CON_I_DATESUPPR`) VALUES
(1, 1, 1, '', ''),
(2, 1, 1, '', ''),
(3, 2, 2, '', ''),
(4, 2, 2, '', ''),
(5, 2, 2, '', ''),
(6, 2, 2, '', '');

-- --------------------------------------------------------

--
-- Structure de la table `itemoption`
--

CREATE TABLE `itemoption` (
  `CON_IO_ITEMOPTION` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `CON_IO_ITEM` int(11) NOT NULL,
  `CON_IO_OPTION` int(11) NOT NULL,
  `CON_IO_VALEUR` varchar(1024) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `option`
--

CREATE TABLE `option` (
  `CON_O_OPTION` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `CON_O_ITEM` int(11) NOT NULL,
  `CON_O_LIBELLE` varchar(100) NOT NULL,
  `CON_O_UNITE` varchar(20) NOT NULL
);

--
-- Déchargement des données de la table `option`
--

INSERT INTO `option` (`CON_O_OPTION`, `CON_O_ITEM`, `CON_O_LIBELLE`, `CON_O_UNITE`) VALUES
(1, 1, 'Poids', 'g'),
(2, 2, 'Poids', 'g');

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `CON_PRO_PRODUIT` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `CON_PRO_CATEGORIE` int(11) NOT NULL,
  `CON_PRO_LIBELLE` varchar(100) NOT NULL
);

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`CON_PRO_PRODUIT`, `CON_PRO_CATEGORIE`, `CON_PRO_LIBELLE`) VALUES
(1, 1, 'Steak Haché'),
(2, 1, 'Rosbeef');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `CON_U_USER` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `CON_U_COMPTE` int(11) NOT NULL,
  `CON_U_NOM` varchar(50) NOT NULL,
  `CON_U_MAIL` varchar(200) NOT NULL
);

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`CON_U_USER`, `CON_U_COMPTE`, `CON_U_NOM`, `CON_U_MAIL`) VALUES
(1, 1, 'Roman', 'eltharin18@gmail.com'),
(2, 1, 'Mag', 'mag@gmail.com'),
(3, 2, 'Cécile', 'cecile@gmail.com'),
(4, 2, 'Olivier', 'olivier@gmail.com');


