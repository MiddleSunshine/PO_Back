# 数据库表结构

```sql
CREATE TABLE `Points` (
                          `ID` int(11) NOT NULL AUTO_INCREMENT,
                          `keyword` varchar(255) COLLATE utf8_bin DEFAULT NULL,
                          `note` text COLLATE utf8_bin,
                          `file` varchar(255) COLLATE utf8_bin DEFAULT NULL,
                          `url` varchar(255) COLLATE utf8_bin DEFAULT NULL,
                          `status` varchar(50) COLLATE utf8_bin DEFAULT NULL,
                          `AddTime` datetime DEFAULT NULL,
                          `LastUpdateTime` datetime DEFAULT NULL,
                          `Point` int(255) DEFAULT '0',
                          `Deleted` int(2) DEFAULT '0',
                          `Favourite` varchar(10) COLLATE utf8_bin DEFAULT NULL,
                          PRIMARY KEY (`ID`),
                          UNIQUE KEY `keyword` (`keyword`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

```sql
CREATE TABLE `Points_Connection` (
                                     `ID` int(11) NOT NULL AUTO_INCREMENT,
                                     `PID` int(11) DEFAULT NULL,
                                     `SubPID` int(11) DEFAULT NULL,
                                     PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

```sql
CREATE TABLE `Willing` (
                           `ID` int(11) NOT NULL AUTO_INCREMENT,
                           `note` text COLLATE utf8_bin,
                           `Point` int(255) DEFAULT '0',
                           `AddTime` datetime DEFAULT NULL,
                           `LastUpdateTime` datetime DEFAULT NULL,
                           `status` varchar(50) COLLATE utf8_bin DEFAULT 'new',
                           PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

```sql
CREATE TABLE `Plans` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `Note` text COLLATE utf8_bin,
  `AddTime` datetime DEFAULT NULL,
  `status` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `UpdateFinishTime` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `Deleted` int(1) DEFAULT NULL,
  `Full_Day_Default` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `Display_In_Table` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Deleted` (`Deleted`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

```sql
CREATE TABLE `Plan_Item2` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `AddTime` datetime DEFAULT NULL,
  `FinishTime` datetime DEFAULT NULL,
  `Note` text COLLATE utf8_bin,
  `PPID` int(11) DEFAULT NULL,
  `Deleted` int(11) DEFAULT '0',
  `PID` int(11) DEFAULT NULL,
  `Full_Day` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `PID` (`PID`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=597 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

```sql
CREATE TABLE `PO`.`GTD`  (
  `ID` int(0) NOT NULL AUTO_INCREMENT,
  `Content` varchar(1000) NULL,
  `note` text NULL,
  `offset` tinyint(255) NULL,
  `PID` int(0) NULL,
  `CategoryID` int(0) NULL,
  `AddTime` datetime(0) NULL DEFAULT NULL,
  `FinishTime` datetime(0) NULL DEFAULT NULL,
  `StartTime` datetime(0) NULL DEFAULT NULL,
  `EndTime` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`),
  INDEX `CategoryID`(`CategoryID`) USING HASH,
  INDEX `FinishTime`(`FinishTime`) USING HASH
);
```

```sql
CREATE TABLE `clock_in` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Year` char(10) COLLATE utf8_bin DEFAULT NULL,
  `Month` char(10) COLLATE utf8_bin DEFAULT NULL,
  `Day` char(10) COLLATE utf8_bin DEFAULT NULL,
  `working_hours` datetime DEFAULT NULL,
  `off_work_time` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

# 前端插件

- [markdown编辑器](https://uiwjs.github.io/react-markdown-editor/)
- [markdown预览器](https://github.com/uiwjs/react-markdown-preview)

