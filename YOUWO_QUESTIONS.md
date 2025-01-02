---
title: YOUWO_QUESTIONS

---

> [TOC]

### Q15

#### 測試集 A - 單一類型測試
```
A1. 只選擇Collection
預期結果：顯示Product和Collection菜單

A2. 只選擇Product
預期結果：不顯示任何菜單

A3. 只選擇Featured Product
預期結果：不顯示任何菜單

A4. 只選擇Featured Collection
預期結果：不顯示任何菜單
```
#### 測試集 B - 雙重組合測試
```
B1. Collection + Product
預期結果：顯示Product和Collection菜單

B2. Featured Collection + Collection
預期結果：顯示All, Product和Collection菜單

B3. Featured Collection + Product
預期結果：不顯示任何菜單

B4. Featured Product + Collection
預期結果：顯示Product和Collection菜單
```
#### 測試集 C - 三重組合測試
```
C1. Featured Collection + Product + Collection
預期結果：顯示All, Product和Collection菜單

C2. Featured Product + Product + Collection
預期結果：顯示All, Product和Collection菜單
```
#### 測試集 D - 全組合測試
```
D1. Featured Product + Featured Collection + Product + Collection
預期結果：顯示All, Product和Collection菜單
```
---


### Q17

#### Inner Join
| UserID | Name | Age | BrandName | ProductName | Price |Buy Time |
| -------- | -------- | -------- | -------- | -------- | -------- | -------- |
| 1 | Prescott Bartl | 27 | Gap | Sweater | 35.99 | 2010/10/02 |
| 3 | Lael Greet | 30 | Nike | Shoes | 125.99 | 2013/06/12 |

#### Right Join
| UserID | Name | Age | BrandName | ProductName | Price |Buy Time |
| -------- | -------- | -------- | -------- | -------- | -------- | -------- |
| 1 | Prescott Bartl | 27 | Gap | Sweater | 35.99 | 2010/10/02 |
| 3 | Lael Greet | 30 | Nike | Shoes | 125.99 | 2013/06/12 |
| 4	| NULL | NULL |	Adidas | Shoes | 105.99 | 2013/08/25 |
| 5	| NULL | NULL | H&M | Jacket | 129.99 | 2013/05/09 |



---

### Q18

#### id (Int(10))
###### 檢查點：
```
必須是整數
數值範圍不能超過10位數
可以使用正則表達式 ^\d{1,10}$ 來驗證
```

date (Date)
###### 檢查點：
```
必須是有效的日期格式
可以使用日期解析函數來驗證是否為有效日期
建議使用 ISO 格式 (YYYY-MM-DD)
```

#### users (Bigint(20))
###### 檢查點：
```
必須是整數
數值範圍必須在 Bigint 允許的範圍內
不能超過20位數字
```

#### revenue 和 rpm (Decimal(13,2))
###### 檢查點：
```
必須是數字
整數部分不能超過11位
小數部分必須剛好是2位
可以使用正則表達式 ^\d{1,11}\.\d{2}$ 來驗證
```

#### landing_page, domain_name, publisher (Varchar(255))
###### 檢查點：
```
字串長度不能超過255個字符
不能包含特殊的危險字符（防止SQL注入）
可以進行基本的文字清理和轉義
```

#### domain_id (int)
###### 檢查點：
```
必須是整數
需要檢查是否在int類型的範圍內（通常是-2147483648到2147483647）
```

#### publisher_id (Int(10))
###### 檢查點：
```
必須是整數
數值範圍不能超過10位數
可以使用與id欄位相同的驗證方法
```


---

### Q20

#### 排查步驟：
1. 立即檢查系統日誌
    - 查看錯誤日誌，尋找異常連接的來源和錯誤信息
    - 查看是否有特定API或端點造成大量連接

2. 監控資源使用情況
    - 檢查服務器CPU、內存使用率
    - 查看數據庫服務器的負載情況
    - 監控網絡流量異常

3. 審查應用程序代碼
    - 檢查連接池配置是否正確
    - 確認是否正確關閉數據庫連接
    - 查找可能的代碼內存洩漏

#### 可能的原因：
1. 連接池配置問題
    - 連接池大小設置不當
    - 連接超時設置不合理
    - 未正確實現連接池回收機制

2. 應用程序問題
    - 代碼中沒有正確關閉數據庫連接
    - 存在內存洩漏導致連接累積
    - 循環中重複創建連接而不釋放

3. 外部因素
    - 突發大量用戶訪問
    - 惡意攻擊或爬蟲行為
    - 批處理作業同時執行

4. 數據庫問題
    - 數據庫效能下降導致連接堆積
    - 死鎖或長時間運行的查詢阻塞其他連接

> 短期解決方案：
>     1. 增加連接池最大連接數
>     2. 實施連接超時機制
>     3. 添加請求限流
> 
> 長期解決方案：
>     1. 優化數據庫查詢效能
>     2. 實施更好的連接池管理
>     3. 添加監控告警機制
>     4. 進行代碼審查，確保連接正確關閉
