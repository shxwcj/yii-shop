/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : yii2basic

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2019-11-19 17:59:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for yii_address
-- ----------------------------
DROP TABLE IF EXISTS `yii_address`;
CREATE TABLE `yii_address` (
  `addressid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(32) NOT NULL DEFAULT '',
  `lastname` varchar(32) NOT NULL DEFAULT '',
  `company` varchar(100) NOT NULL DEFAULT '',
  `address` text,
  `postcode` char(6) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `telephone` varchar(20) NOT NULL DEFAULT '',
  `userid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`addressid`),
  KEY `shop_address_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收货地址';

-- ----------------------------
-- Records of yii_address
-- ----------------------------

-- ----------------------------
-- Table structure for yii_admin
-- ----------------------------
DROP TABLE IF EXISTS `yii_admin`;
CREATE TABLE `yii_admin` (
  `adminid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `adminuser` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员账号',
  `adminpass` char(32) NOT NULL DEFAULT '' COMMENT '管理员密码',
  `adminemail` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员电子邮箱',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `loginip` bigint(20) NOT NULL DEFAULT '0' COMMENT '登录IP',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`adminid`),
  UNIQUE KEY `shop_admin_adminuser_adminpass` (`adminuser`,`adminpass`),
  UNIQUE KEY `shop_admin_adminuser_adminemail` (`adminuser`,`adminemail`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户表';

-- ----------------------------
-- Records of yii_admin
-- ----------------------------
INSERT INTO `yii_admin` VALUES ('1', 'admin', '202cb962ac59075b964b07152d234b70', 'shop@imooc.com', '1574142006', '2130706433', '1569499207');

-- ----------------------------
-- Table structure for yii_cart
-- ----------------------------
DROP TABLE IF EXISTS `yii_cart`;
CREATE TABLE `yii_cart` (
  `cartid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `productid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `productnum` int(10) unsigned NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `userid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cartid`),
  KEY `shop_cart_productid` (`productid`),
  KEY `shop_cart_userid` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='购物车';

-- ----------------------------
-- Records of yii_cart
-- ----------------------------

-- ----------------------------
-- Table structure for yii_category
-- ----------------------------
DROP TABLE IF EXISTS `yii_category`;
CREATE TABLE `yii_category` (
  `cateid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '',
  `parentid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cateid`),
  KEY `shop_category_parentid` (`parentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='分类表';

-- ----------------------------
-- Records of yii_category
-- ----------------------------
INSERT INTO `yii_category` VALUES ('1', '移动手机', '0', '1569504439');
INSERT INTO `yii_category` VALUES ('3', 'OPPO R17', '1', '1569507076');

-- ----------------------------
-- Table structure for yii_migration
-- ----------------------------
DROP TABLE IF EXISTS `yii_migration`;
CREATE TABLE `yii_migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of yii_migration
-- ----------------------------
INSERT INTO `yii_migration` VALUES ('m000000_000000_base', '1574134319');
INSERT INTO `yii_migration` VALUES ('m191119_031735_create_shop_admin_table', '1574134322');
INSERT INTO `yii_migration` VALUES ('m191119_033344_drop_shop_admin_table', '1574134492');

-- ----------------------------
-- Table structure for yii_order
-- ----------------------------
DROP TABLE IF EXISTS `yii_order`;
CREATE TABLE `yii_order` (
  `orderid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `addressid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  `expressid` int(10) unsigned NOT NULL DEFAULT '0',
  `expressno` varchar(50) NOT NULL DEFAULT '',
  `tradeno` varchar(100) NOT NULL DEFAULT '',
  `tradeext` text,
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`orderid`),
  KEY `shop_order_userid` (`userid`),
  KEY `shop_order_addressid` (`addressid`),
  KEY `shop_order_expressid` (`expressid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='订单表';

-- ----------------------------
-- Records of yii_order
-- ----------------------------
INSERT INTO `yii_order` VALUES ('1', '1', '1', '3999.00', '220', '0', '75313173776108', '1131920105', null, '1574142443', '2019-11-19 16:40:33');
INSERT INTO `yii_order` VALUES ('2', '1', '1', '3999.00', '100', '1', '1131922125', '1131923105', null, '1574142924', '2019-11-19 15:43:47');
INSERT INTO `yii_order` VALUES ('3', '1', '1', '3999.00', '220', '1', '1131545231', '1131545234', null, '1574144708', '2019-11-19 15:44:42');

-- ----------------------------
-- Table structure for yii_order_detail
-- ----------------------------
DROP TABLE IF EXISTS `yii_order_detail`;
CREATE TABLE `yii_order_detail` (
  `detailid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `productid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `productnum` int(10) unsigned NOT NULL DEFAULT '0',
  `orderid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`detailid`),
  KEY `shop_order_detail_productid` (`productid`),
  KEY `shop_order_detail_orderid` (`orderid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='订单详情表';

-- ----------------------------
-- Records of yii_order_detail
-- ----------------------------
INSERT INTO `yii_order_detail` VALUES ('1', '1', '3999.00', '1', '1', '1574142443');
INSERT INTO `yii_order_detail` VALUES ('2', '1', '3999.00', '1', '2', '1574142924');
INSERT INTO `yii_order_detail` VALUES ('3', '1', '3999.00', '1', '3', '1574144708');

-- ----------------------------
-- Table structure for yii_product
-- ----------------------------
DROP TABLE IF EXISTS `yii_product`;
CREATE TABLE `yii_product` (
  `productid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cateid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `descr` text,
  `num` int(10) unsigned NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cover` varchar(200) NOT NULL DEFAULT '',
  `pics` text,
  `issale` enum('0','1') NOT NULL DEFAULT '0',
  `ishot` enum('0','1') NOT NULL DEFAULT '0',
  `istui` enum('0','1') NOT NULL DEFAULT '0',
  `saleprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ison` enum('0','1') NOT NULL DEFAULT '1',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`productid`),
  KEY `shop_product_cateid` (`cateid`),
  KEY `shop_product_ison` (`ison`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='商品表';

-- ----------------------------
-- Records of yii_product
-- ----------------------------
INSERT INTO `yii_product` VALUES ('1', '3', 'OPPO Reno', 'OPPO Reno<br>OPPO Reno<br>OPPO Reno<br>OPPO Reno<br>', '97', '3999.00', 'o7zgluxwg.bkt.clouddn.com/5dd270bb53408', '[]', '0', '1', '0', '0.01', '1', '0');
INSERT INTO `yii_product` VALUES ('3', '1', 'OPPO r17', 'OPPO r17<br>OPPO r17<br>OPPO r17<br>', '500', '3999.00', 'o7zgluxwg.bkt.clouddn.com/5dd3537a8c551', '{\"5dd3537ac8e11\":\"o7zgluxwg.bkt.clouddn.com\\/5dd3537ac8e11\"}', '0', '1', '0', '0.01', '1', '0');

-- ----------------------------
-- Table structure for yii_profile
-- ----------------------------
DROP TABLE IF EXISTS `yii_profile`;
CREATE TABLE `yii_profile` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `truename` varchar(32) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `age` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `sex` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date NOT NULL DEFAULT '2016-01-01' COMMENT '生日',
  `nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `company` varchar(100) NOT NULL DEFAULT '' COMMENT '公司',
  `userid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户的ID',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_profile_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户详情表';

-- ----------------------------
-- Records of yii_profile
-- ----------------------------

-- ----------------------------
-- Table structure for yii_user
-- ----------------------------
DROP TABLE IF EXISTS `yii_user`;
CREATE TABLE `yii_user` (
  `userid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` varchar(32) NOT NULL DEFAULT '',
  `userpass` char(32) NOT NULL DEFAULT '',
  `useremail` varchar(100) NOT NULL DEFAULT '',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `shop_user_username_userpass` (`username`,`userpass`),
  UNIQUE KEY `shop_user_useremail_userpass` (`useremail`,`userpass`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='会员表';

-- ----------------------------
-- Records of yii_user
-- ----------------------------
INSERT INTO `yii_user` VALUES ('1', 'admin', '202cb962ac59075b964b07152d234b70', 'shop@imooc.com', '1569499207');
