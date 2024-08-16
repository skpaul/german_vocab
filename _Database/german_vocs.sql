/*
 Navicat Premium Data Transfer

 Source Server         : XAMPP
 Source Server Type    : MySQL
 Source Server Version : 100417
 Source Host           : localhost:3306
 Source Schema         : german_vocs

 Target Server Type    : MySQL
 Target Server Version : 100417
 File Encoding         : 65001

 Date: 16/08/2024 11:41:52
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for antonyms
-- ----------------------------
DROP TABLE IF EXISTS `antonyms`;
CREATE TABLE `antonyms`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `wordId` int NULL DEFAULT NULL COMMENT 'id column in words table.',
  `antonymWordId` int NULL DEFAULT NULL COMMENT 'id column in words table.',
  `createdOn` date NULL DEFAULT curdate,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for synonyms
-- ----------------------------
DROP TABLE IF EXISTS `synonyms`;
CREATE TABLE `synonyms`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `wordId` int NULL DEFAULT NULL COMMENT 'id column in words table.',
  `synonymWordId` int NULL DEFAULT NULL COMMENT 'id column in words table.',
  `createdOn` date NULL DEFAULT curdate,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for words
-- ----------------------------
DROP TABLE IF EXISTS `words`;
CREATE TABLE `words`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `english` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `german` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `banglaPro` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'pronunciation in bangla',
  `createdOn` date NULL DEFAULT curdate COMMENT 'asdf',
  `updatedOn` date NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
