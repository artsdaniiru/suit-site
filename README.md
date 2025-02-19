# Suit-Site

このプロジェクトはカスタムスーツのオンラインストア用のVue.jsアプリケーションです。バックエンドはPHPを使用し、データベースと連携して動作します。

## 必要な環境

- [Node.js](https://nodejs.org/)（最新版を推奨）
- Vue CLI

## インストール

まず、Vue CLI をグローバルにインストールします。
```sh
npm install -g @vue/cli
```

その後、プロジェクトをセットアップします。
```sh
npm install && node first-install.js
```

## データベースおよびバックエンドAPIの設定

`.env` ファイルを作成し、開発環境または本番環境ごとに設定を行います。

### `.env` ファイルの例

```env
VUE_APP_BACKEND_URL=http://localhost:8181/public
DB_HOST=localhost
DB_USER=user
DB_PASS=pass
DB_NAME=name
VPS_HOST=host
VPS_USERNAME=user
SSH_PASSWORD=pass
REMOTE_DIR=dirname
```

## 開発環境での実行

開発環境でホットリロードを有効にしてサーバーを起動するには、以下のコマンドを実行します。
```sh
npm run serve
```

## 本番環境向けのビルド

本番環境用にコードを最適化してビルドするには、以下のコマンドを実行します。
```sh
npm run build
```

## VPSサーバーへのアップロード

VPSサーバーにデプロイするには、以下のコマンドを実行します。
```sh
node uploader.js
```

## コードのリントと修正

コードの品質を維持するため、以下のコマンドでリントを実行できます。
```sh
npm run lint
```

## バックアップ

データベースおよび画像のバックアップは、以下のファイルに保存されています。
- `k23b.sql`（データベースバックアップ）
- `images.zip`（画像バックアップ）

## バックエンドについて

`public` フォルダ内に `backend` フォルダがあり、すべてのバックエンドファイルが格納されています。
