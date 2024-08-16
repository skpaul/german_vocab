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

 Date: 16/08/2024 11:27:40
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
  `antonymsId` int NULL DEFAULT NULL COMMENT 'id column in words table.',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of antonyms
-- ----------------------------

-- ----------------------------
-- Table structure for synonyms
-- ----------------------------
DROP TABLE IF EXISTS `synonyms`;
CREATE TABLE `synonyms`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `wordId` int NULL DEFAULT NULL COMMENT 'id column in words table.',
  `synonymId` int NULL DEFAULT NULL COMMENT 'id column in words table.',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of synonyms
-- ----------------------------

-- ----------------------------
-- Table structure for words
-- ----------------------------
DROP TABLE IF EXISTS `words`;
CREATE TABLE `words`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `english` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `german` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `banglaPro` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'pronunciation in bangla',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of words
-- ----------------------------
INSERT INTO `words` VALUES (1, 'I', 'Ich', 'ঈশ');

SET FOREIGN_KEY_CHECKS = 1;
