# 環境構築

1. Dockerを起動する

2. プロジェクト直下で、以下のコマンドを実行する

```
make init
```

## SQLデータ
.envファイルの環境変数は以下になります<br>
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

## メール認証
mailHogを使用しています。<br>
.envファイルの環境変数は以下になります<br>

``` text
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="test@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## テーブル仕様
### usersテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| name | varchar(255) |  |  | ◯ |  |
| email | varchar(255) |  | ◯ | ◯ |  |
| email_verified_at | timestamp |  |  |  |  |
| password | varchar(255) |  |  | ◯ |  |
| remember_token | varchar(100) |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### adminsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| name | varchar(255) |  |  | ◯ |  |
| email | varchar(255) |  | ◯ | ◯ |  |
| password | varchar(255) |  |  | ◯ |  |
| remember_token | varchar(100) |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### attendancesテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| user_id | bigint |  |  | ◯ | users(id) |
| work_date | date |  |  | ◯ |  |
| clock_in | time |  |  |  |  |
| clock_out | time |  |  |  |  |
| working_hours | time |  |  |  |  |
| total_break | time |  |  |  |  |
| status | string |  |  | ◯ |  |
| reason | text |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### breaksテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| attendance_id | bigint |  |  | 〇 | attendances(id) |
| break_start | time |  |  |  |  |
| break_end | time |  |  |  |  |
| break_hours | time |  |  |  |  |
| break_seconds | integer |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### attendance_correctionsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| user_id | bigint |  |  | ◯ | users(id) |
| attendance_id | bigint |  |  | ◯ | attendances(id) |
| clock_in_correction | time |  |  |  |  |
| clock_out_correction | time |  |  |  |  |
| requested_date | date |  |  | ◯ |  |
| approval_status | string |  |  | ◯ |  |
| reason_correction | string |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### break_correctionsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| attendance_correction_id | bigint |  |  | 〇 | attendances(id) |
| break_id | bigint |  |  |  | breaks(id) |
| break_start_correction | time |  |  |  |  |
| break_end_correction | time |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

## ER図
![alt](ER.png)

## テストアカウント
name: 西 伶奈  
email: reina.n@coachtech.com  
password: password1
-------------------------
name: 山田 太郎  
email: taro.y@coachtech.com  
password: password2
-------------------------
name: 増田 一世  
email: issei.m@coachtech.com  
password: password3
-------------------------
name: 山本 敬吉  
email: keikichi.y@coachtech.com  
password: password4
-------------------------
name: 秋田 朋美  
email: tomomi.a@coachtech.com  
password: password5
-------------------------
name: 中西 教夫  
email: norio.n@coachtech.com  
password: password6
-------------------------
name: 管理者  
email: admin@test.com  
password: 12345678  
-------------------------
<br>
勤怠情報の例として、西 伶奈さんの2025年11月分勤怠と11月3日に 3名分の勤怠が登録されています。<br>

## PHPUnitを利用したテストに関して
以下のコマンドを実行:  
```
//MySQLコンテナ上でテスト用データベースの作成
docker-compose exec mysql bash

//MySQLコンテナ上
mysql -u root -p

//パスワードはrootと入力
CREATE DATEBASE demo_test;
SHOW DATABASES;

SHOW DATABASES;入力後、demo_testが作成されていれば成功
exitでコンテナを抜ける

//テスト用の.envファイル作成
docker-compose exec php bash
cp .env .env.testing

//※コマンドでファイル権限を与える必要がある
sudo chown -R $USER:$USER src/

.env.testingを以下の環境変数に書き換えをする

// キャッシュのクリアとマイグレーションコマンドの実行
php artisan config:clear
php artisan migrate:fresh --env=testing

vendor/bin/phpunit tests/Feature/ファイル名.phpで
テストの実行ができます
```

.env.testingファイルの環境変数は以下になります<br>
``` text
APP_NAME=Laravel
APP_ENV=test
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root

```

## 使用技術(実行環境)
- PHP8.2.29
- Laravel8.83.29
- MySQL8.0.26

## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
- MailHog:：http://localhost:8025/