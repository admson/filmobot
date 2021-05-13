SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
--
-- Структура таблицы `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
    `id` int(11) NOT NULL,
    `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `films`
--

CREATE TABLE IF NOT EXISTS `films` (
    `id` int(11) NOT NULL,
    `hash` varchar(255) DEFAULT NULL,
    `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    `photo` varchar(255) DEFAULT NULL,
    `trailer` varchar(255) DEFAULT NULL,
    `video` varchar(255) DEFAULT NULL,
    `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `year` varchar(255) DEFAULT NULL,
    `categories` text,
    `private_msg_id` int(11) DEFAULT NULL,
    `public_msg_id` int(11) DEFAULT NULL,
    `public2_msg_id` int(11) DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `_accounts`
--

CREATE TABLE IF NOT EXISTS `_accounts` (
    `id` int(11) NOT NULL,
    `status` int(11) NOT NULL DEFAULT '0',
    `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    `chat_id` int(11) NOT NULL,
    `lang` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `banned` int(1) NOT NULL DEFAULT '0',
    `admin` int(1) NOT NULL DEFAULT '0',
    `menu` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `last_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `_dialogs`
--

CREATE TABLE IF NOT EXISTS `_dialogs` (
    `id` int(11) NOT NULL,
    `hash` varchar(255) NOT NULL,
    `chat_id` int(11) NOT NULL,
    `menu` varchar(64) DEFAULT NULL,
    `data` int(11) DEFAULT NULL,
    `data2` varchar(255) DEFAULT NULL,
    `page` int(11) DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `_reactions`
--

CREATE TABLE IF NOT EXISTS `_reactions` (
    `id` int(11) NOT NULL,
    `item_id` int(11) NOT NULL,
    `chat_id` varchar(64) NOT NULL,
    `react` int(1) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--
ALTER TABLE `categories`
    ADD PRIMARY KEY (`id`);
ALTER TABLE `films`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `_accounts`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `_dialogs`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `_reactions`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `categories`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `films`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `_accounts`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `_dialogs`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `_reactions`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;