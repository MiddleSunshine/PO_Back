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
                          PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

```sql
CREATE TABLE `Points_Connection` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PID` int(11) DEFAULT NULL,
  `SubPID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

# 前端插件

- [markdown编辑器](https://uiwjs.github.io/react-markdown-editor/)
- [markdown预览器](https://github.com/uiwjs/react-markdown-preview)

