-- ============================================================
-- Base 2026_accestracabilite : thème d'une question (question.id_theme)
--
-- Le code depuis « Refonte de l'Espace Traçabilité » (QuestionModel, AdminQuestionController,
-- AdminQuizController) lit et écrit question.id_theme. Sans cette colonne, la page
-- d'ajout / de modification d'un quiz plante (Unknown column 'id_theme').
--
-- 1. Ajout de la colonne (vide au départ)
-- 2. Remplissage : thème du quiz où la question apparaît
--    (si elle est dans des quiz de plusieurs thèmes, le plus petit id de thème est gardé).
--    Une question qui n'est dans aucun quiz reste sans thème : à choisir dans le formulaire.
-- ============================================================

ALTER TABLE `question`
    ADD COLUMN `id_theme` INT(11) NULL DEFAULT NULL AFTER `id`,
    ADD KEY `idx_question_theme` (`id_theme`);

UPDATE `question` q
JOIN (
    SELECT qq.id_question, MIN(z.id_theme) AS id_theme
    FROM `quiz_question` qq
    JOIN `quiz` z ON z.id = qq.id_quiz
    GROUP BY qq.id_question
) t ON t.id_question = q.id
SET q.id_theme = t.id_theme;
