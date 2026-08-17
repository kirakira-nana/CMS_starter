# LightCMS
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.3.0-8892BF.svg)](http://www.php.net/)

## プロジェクト概要
`lightCMS` は軽量な CMS です。汎用の管理画面フレームワークとしても使えます。ユーザー管理、権限管理、ログ管理、メニュー管理など、管理画面に必要な機能を備え、モデル管理やカテゴリ管理といった CMS の定番機能もあります。**コードの一括生成** で、特定モデルの CRUD をすばやく作れます。

`lightCMS` は `Laravel 13.x` ベースで、フロントは `layui` です。

デモ：[LightCMS Demo](http://lightcms.bituier.com/admin/login)。ログイン情報：admin/admin。重要なデータの保存や削除はしないでください。データベースは定期的に初期化されます。

作者：[Nana](https://github.com/kirakira-nana)

ブランチと Laravel バージョン：

| ブランチ | Laravel | メンテナンス | 備考 |
|:-:|:-:|:-:|:-:|
| 13.x | 13.x | はい | |
| 12.x | 12.x | はい | |
| 11.x | 11.x | いいえ | |
| 10.x | 10.x | いいえ | |
| 9.x | 9.x | いいえ | |
| 8.x | 8.x | いいえ | |
| 7.x | 7.x | いいえ | |
| master | 6.x | いいえ | |
| 5.5 | 5.5 | いいえ | |

## 機能一覧
管理画面：
* `RBAC` による権限管理
* 管理者・ログ・メニュー管理
* カテゴリ管理
* タグ管理
* 設定管理
* モデル、モデルフィールド、モデルコンテンツ管理（業務モデルを管理画面から定義できます）
* 会員管理
* コメント管理
* Trie アルゴリズムによるセンシティブワード検出
* 通常モデルの CRUD コード一括生成

フロント：
* ユーザー登録・ログイン（WeChat / QQ / Weibo の外部ログインを含む）
* モデルコンテンツの詳細ページ・一覧ページ
* コメント関連

ほかの機能は実際に触って確認してください。

## 管理画面プレビュー
![ホーム](https://user-images.githubusercontent.com/2555476/54804611-16fa4900-4caf-11e9-885e-7f5c0dac7ce4.png)

![システム管理](https://user-images.githubusercontent.com/2555476/54804599-0ea20e00-4caf-11e9-8d10-526aca358916.png)

## 動作環境
`linux/windows & nginx/apache/iis & mysql 5.5+ & php 8.3.0+`

* PHP >= 8.3.0
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension
* GD PHP Extension
* curl 7.34.0 以上

**注意**

* キャッシュ、キュー、セッションが redis ドライバの場合は、redis と PHP redis 拡張も必要です
* `PHP` に `opcache` がある場合は、`opcache.save_comments` と `opcache.load_comments` を有効にしてください（既定は有効）。無効だと[メニュー自動取得](#メニュー自動取得)が動きません

## 導入

### コード取得と依存関係のインストール
先に [composer](https://getcomposer.org/) を入れてください。
```bash
cd /data/www
git clone git@github.com:kirakira-nana/LightCMS.git
cd LightCMS
composer install
```
### 設定と初期化
`storage/` と `bootstrap/cache/` に書き込み権限が必要です。
```bash
# 777 は説明用です。実際は Web サーバーに書き込み権限があれば十分です
sudo chmod 777 -R storage/ bootstrap/cache/
```
環境設定ファイルを作り、データベースなどを設定します。
```bash
cp .env.example .env
```
初期化：
```bash
php artisan migrate --seed
```

### Web サーバー設定（Nginx の例）
```
server {
    listen 80;
    server_name light.com;
    root /data/www/lightCMS/public;
    index index.php index.html index.htm;
    
    add_header X-Frame-Options "SAMEORIGIN";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~* \.(txt|doc|pdf|rar|gz|zip|docx|exe|xlsx|ppt|pptx)$ {
        add_header Content-Disposition Attachment;
        add_header X-Content-Type-Options nosniff;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        # 環境ごとに読み込む設定ファイルを変えられます。未設定なら .env を読みます。
        #fastcgi_param   APP_ENV pro;
        include fastcgi_params;
    }
}
```

### 管理画面ログイン
URL：`/admin/login`

既定ユーザー（スーパーユーザー。権限制限を受けません）：admin/admin

## 権限管理
ロールベースの権限管理です。ロールを作り、権限を割り当て、ユーザーにロールを付けるだけです。ここでの権限はメニューそのものです。1 メニューが `laravel` の 1 ルート、つまり 1 操作に対応します。

### メニュー自動取得
ルートに対応するコントローラへ、決められた形式のコメントを書くと、[メニュー管理](/admin/menus) でメニューを自動追加・更新できます。例：
```php
/**
 * ロール管理-ロール一覧
 *
 * メソッドの 1 行目コメントをメニュー名・グループ名にします。形式：グループ名-メニュー名。
 * グループ名が無い場合は、そのままメニュー名になり、グループ名は空です。
 * コメントが無い場合は uri をメニュー名にし、グループ名は空です。
 */
public function index()
{
    $this->breadcrumb[] = ['title' => 'ロール一覧', 'url' => ''];
    return view('admin.role.index', ['breadcrumb' => $this->breadcrumb]);
}
```

メニューは自動取得できますが、階層は管理画面で手作業で設定します。

## 設定管理
まず `config/light.php` の `light_config` を `true` にしてください。

そのあと [設定管理](/admin/configs) で項目を追加・編集すると、`laravel` の `config` で取得できます。
```php
// key が SITE_NAME の設定値を取得
$siteName = config('light_config.SITE_NAME');
```
グローバル関数 `function getConfig($key, $default = null)` でも取得できます。

## タグ管理
モデルコンテンツへのタグ付けはよく使う機能で、`lightCMS` に組み込みがあります。モデルフィールド追加時にフォーム種別を `タグ入力枠` にしてください。

中間テーブル（content_tags）でタグとコンテンツの多対多を実現しています。

## モデル管理
管理画面からモデルを作り、テーブル項目をカスタムできます。フィールド設定後は、CRUD はシステム側で用意されます。

独自のフォーム検証が必要なら、`app/Http/Request/Admin/Entity` にフォームリクエストクラスを置きます。クラス名は **モデル名+Request** です。例：`User` なら `UserRequest`。

新規/編集テンプレートを独自にする場合は、`app/resources/views/admin/content` に **モデル名_add.blade.php** を置きます。例：`user_add.blade.php`。

保存・更新ロジックを独自にする場合は、`app/Http/Controllers/Admin/Entity` に **モデル名+Controller** を置きます。`save` / `update` は `app/Http/Controllers/Admin/ContentController` を参考にしてください。一覧を独自にする場合も、同じ規則で `index` と `list` を定義します。

コンテンツの追加・更新・削除時にはイベントが飛びます。監視して業務処理を足せます。

| イベント名 | 引数 | タイミング | 備考 |
|:-:|:-:|:-:|:-:|
| App\Events\ContentCreating | Illuminate\Http\Request $request, App\Model\Admin\Entity $entity | 追加前 | |
| App\Events\ContentCreated | App\Model\Admin\Content $content, App\Model\Admin\Entity $entity | 追加後 | |
| App\Events\ContentUpdating | Illuminate\Http\Request $request, App\Model\Admin\Entity $entity | 更新前 | |
| App\Events\ContentUpdated | Array $id, App\Model\Admin\Entity $entity | 更新後 | $id は更新 ID の集合 |
| App\Events\ContentDeleting | Illuminate\Support\Collection $contents, App\Model\Admin\Entity $entity | 削除前 | 削除対象の Content 集合 |
| App\Events\ContentDeleted | Illuminate\Support\Collection $contents, App\Model\Admin\Entity $entity | 削除後 | 削除対象の Content 集合 |
| App\Events\ContentCreateShow | App\Model\Admin\Entity $entity, App\Foundation\ViewData $viewData | 追加フォーム表示前 | addCss / addJs / addTemplate で差し込み |
| App\Events\ContentEditShow | App\Model\Admin\Entity $entity, Illuminate\Database\Eloquent\Model $model, App\Foundation\ViewData $viewData | 更新フォーム表示前 | addCss / addJs / addTemplate で差し込み |
| App\Events\ContentListShow | int $entityId | 一覧表示前 | 表示項目や検索項目のカスタム用 |
| App\Events\ContentListDataReturning | int $entityId, Illuminate\Contracts\Pagination\Paginator $data | 一覧 API 返却前 | 返却データのカスタム用 |

### モデルフィールドのフォーム種別
リモート検索対応の `select` は、次の JSON を返してください。code が 0 なら成功です。
```json
{
    "code": 0,
    "msg": "success",
    "data": [
        {"name":"東京","value":1,"selected":"","disabled":""},
        {"name":"大阪","value":2,"selected":"","disabled":""},
        {"name":"名古屋","value":3,"selected":"selected","disabled":""},
        {"name":"福岡","value":4,"selected":"","disabled":"disabled"},
        {"name":"札幌","value":5,"selected":"","disabled":""}
    ]
}
```

短テキスト（input、オートコンプリート）は次の形式です。
```json
{
    "suggestions": [
        "cms",
        "cmsとは",
        "コンテンツ管理"
    ]
}
```

`select` 複数選択は、既定では半角カンマ区切りで保存します。フィールド型を符号なし整数にすると、選択値の合計を保存します（値が整数である前提）。

### 検索フィールド（$searchField）
一覧に検索項目を出せます。例：
```php
    public static $searchField = [
        'name' => 'ユーザー名',
        'status' => [
            'showType' => 'select',
            'searchType' => '=',
            'title' => '状態',
            'enums' => [
                0 => '無効',
                1 => '有効',
            ],
        ],
        'recommend' => [
            'showType' => 'select',
            'searchType' => 'whereRaw',
            'searchCondition' => 'recommend & ? = ?',
            'title' => 'おすすめ枠',
            'enums' => [
                1 => 'おすすめ1',
                2 => 'おすすめ2',
                4 => 'おすすめ3',
            ],
        ],
        'created_at' => [
            'showType' => 'datetime',
            'title' => '作成日時'
        ]
    ];
```

### 一覧フィールド（$listField）
```php
    public static $listField = [
        'pid' => ['title' => '親ID', 'width' => 80],
        'entityName' => ['title' => 'モデル', 'width' => 100],
        'userName' => ['title' => 'ユーザー名', 'width' => 100],
        'content' => ['title' => '内容', 'width' => 400],
        'reply_count' => ['title' => '返信数', 'width' => 80, 'sort' => true],
        'like' => ['title' => 'いいね', 'width' => 80, 'sort' => true],
        'dislike' => ['title' => 'よくないね', 'width' => 80, 'sort' => true],
    ];
```

### 操作項目（$actionField）
```php
    public static $actionField = [
        'chapterUrl' => ['title' => '章', 'description' => 'この小説の全章'],
    ];
```

### 並び替え（$sortFields）
```php
    public static $sortFields = [
        'updated_at,desc' => '更新日時（降順）',
        'id,asc' => 'id（昇順）',
    ];
```

### ボタン（$btnField）
```php
    public static $btnField = [
        [
            'title' => 'Google',
            'description' => '検索エンジン',
            'url' => 'https://www.google.com',
            'target' => '_blank',
            'class' => '',
        ],
    ];
```

> 独自モデルは `App\Model\Admin\Model` を継承すると、上記設定を書きやすいです。

> イベントを監視してこれらの属性を設定すると、表示を柔軟に変えられます。

## システムログ
管理画面の操作を記録する簡単なログがあります。実装は `Log` ミドルウェアを参照してください。

[タスクスケジューラ](https://laravel.com/docs/13.x/scheduling#introduction) でログ掃除ができます。crontab 例：
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

`log_async_write` で非同期書き込みを有効にできます（既定は無効）。有効時はキューワーカーが必要です。
```bash
php artisan queue:work
```

## コード一括生成
通常モデルの CRUD を次の artisan コマンドで生成できます。
```bash
# config がモデル名、設定 が日本語名
php artisan light:basic config 設定
```
成功すると次のファイルができます（ディレクトリに書き込み権限が必要です）。

* routes/auto/config.php  
  CRUD ルート。`routes/auto/` は自動読み込みです。
* app/Model/Admin/Config.php  
  [$searchField](#検索フィールドsearchfield) と [$listField](#一覧フィールドlistfield)
* app/Repository/Admin/ConfigRepository.php  
  一覧データの `list` メソッド。必要なら `transform` で変換します。
* app/Http/Controllers/Admin/ConfigController.php  
  `$formNames` は新規/編集フォームのホワイトリストです。必須です。
* app/Http/Requests/Admin/ConfigRequest.php  
  フォーム検証
* resources/views/admin/config/index.blade.php  
  一覧と検索
* resources/views/admin/config の新規/編集ビュー  
  基本骨格のみ。フィールド表示は自分で足します。

生成ルートをメニューに出すには、[メニュー管理](/admin/menus) で **メニュー自動更新** を押します。

## センシティブワード検出
投稿内容の検査には `checkSensitiveWords` を使います。
```php
$result = checkSensitiveWords('禁止ワードの例');
print_r($result);
```

## 画像アップロード
既定はローカル保存です。クラウドへ上げる場合は `config/light.php` の `image_upload` を参照し、`App\Contracts\ImageUpload` を実装してください。戻り値は次の形に揃えます。
```json
{
    "code": 200,
    "state": "SUCCESS",
    "msg": "",
    "url": "xxx"
}
```

## コア関数・メソッド
カスタム開発の手がかりです。
***
メソッド：App\Repository\Admin\CategoryRepository::tree()

分類のツリーを返します。`path` は上位分類の連鎖です。例えば `path` が `[1, 2]` なら、親は「ゲーム」「シューティング」です。
***
例外：`App\Exceptions\InvalidAppDataException`

この例外を投げると、メッセージ付きの案内ページへ遷移します。実行時エラーの画面表示向けです。

***
`App\Repository\Admin\CategoryRepository` の分類メソッド：
```
/**
 * 指定階層の全分類。ルート階層は 0
 */
public static function levelCategories(int $level = 0, $tree = null): Collection

/**
 * 指定分類の葉ノード。$categoryId が 0 なら全葉ノード
 */
public static function leafCategories(int $categoryId = 0, $tree = null): Collection

/**
 * 指定分類の全親。親が無ければ空配列
 */
public static function parentCategories(int $categoryId, $tree = null): array
```

## フロント
### ユーザー登録・ログイン
簡単な登録ログインを内蔵し、WeChat / QQ / Weibo に対応します。設定は `config/light.php` を見てください。

## 作者

- [Nana](https://github.com/kirakira-nana) – 実装・ドキュメント
