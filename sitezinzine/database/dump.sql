
--

INSERT INTO `user` (`id`, `username`, `roles`, `password`, `email`, `is_verified`) VALUES
(1, 'drelin04', '{\"3\":\"ROLE_SUPER_ADMIN\"}', '$2y$13$CiwgqLoTVP6baQZ9uYvlCuDLvw.Q8bTP3YwdpblUxjRAslzun//dG', 'drelin04@hotmail.fr', 1),
(2, 'carotte44', '[]', '$2y$13$bgzUOBUQpsIHPPUJ84gw8OG8wlrWXW/tsOm3zSaYwq8JaAR2o8grC', 'cirier.c@gmail.com', 0),
(3, 'joelle', '[]', '$2y$13$MeE6/5O.ZRSTG3vFTr9GMeZUjszu0E/dVL65TTMc.BuROSxAkL2.q', 'joviale@poivron.org', 0),
(4, 'eloise', '[]', '$2y$13$UYPQoN7TMkBIYW2s8NHTNO5j.1f4XVKql4y3n/oRCDTGM1MbUyfEK', 'test@test.com', 0),
(5, 'test', '[\"ROLE_USER\",\"ROLE_EDITOR\"]', '$2y$13$NXJtCDbJrK5ckGrq1VIQouRE7Pi/gwkxlaVNxEMrKvDKEdna3sUci', 'testouille@test.com', 0),
(6, 'Elerina', '[]', '$2y$13$NB1q9Nzfi7tX9.nqDuotgu.2W8ihqJvLbtnCi1Z/zgdQfQW/FzTo6', 'elerina@test.com', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_USERNAME` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
