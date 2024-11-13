<template>

    <div >
    
      <h2>マイページ</h2>

      <div class="account-view ">

        <div class="account-inf">
          <h2>個人情報</h2>
          <div class="inf-date">
            <label for="">姓</label>
            <input placeholder="例）山田" type="text">
          </div>
          <div class="inf-date">
            <label for="">名</label>
            <input placeholder="例）太郎" type="text">
          </div>
          <div class="inf-date">
            <label for="">メール</label>
            <input placeholder="例）サイトーズ@mail.jp" type="text">
          </div>
          <div class="inf-date">
            <label for="">身長</label>
            <input placeholder="身長(cm)を入力してください" type="text">
          </div>
          <div class="inf-date">
            <label for="">肩幅</label>
            <input placeholder="肩幅(cm)を入力してください" type="text">
          </div>
          <div class="inf-date">
            <label for="">ウェストサイズ</label>
            <input placeholder="ウェスト(cm)を入力してください" type="text">
          </div>
          <button class="button">送信</button>

        </div>

        <div class="account-address">
          <div class="adress-wrap">

            <div class="adress-card">
              <h2>山田太郎</h2>
              <p>240-0062, 神奈川県横浜市保土ケ谷区岡沢町, ４０１－６</p>
              <div class="tel">
                <span>電話番号: </span>
                <span>080729709776</span>
              </div>
              <span class="icon-close"></span>
              <span class="icon-pencil"></span>

            </div>
            <div class="adress-card">
              <h2>山田太郎</h2>
              <p>240-0062, 神奈川県横浜市保土ケ谷区岡沢町, ４０１－６</p>
              <div class="tel">
                <span>電話番号: </span>
                <span>080729709776</span>
              </div>
              <span class="icon-close"></span>
              <span class="icon-pencil"></span>

            </div>


            <div class="add-card">
              <span class="icon-plus"></span>
              <p>新しいお届け先住所を追加する</p>
            </div>

            </div>
        </div>
      </div>
    
    </div>
    
    
    
    </template>
      
    <script>
    
    </script>
      
    <style lang="scss" scoped>

    h2{text-align: left;}

    .account-view{
      display: flex;
      justify-content: space-between;
      margin-bottom: 64px;
      
    }

    .account-inf{
      border: 1px solid #d9d9d9;
      border-radius: 8px;
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 24px;
      width: 100%;
      max-width: 550px;
      
      .inf-date{
        max-width: 350px;
        display: flex;
        flex-direction: column;
      }
    }
    
    
    .adress-card{
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    position: relative;
    padding: 32px;
    box-sizing: border-box;
    width: 100%;
    margin-bottom: 32px;
    
    .icon-close{
      width: min-content;
      position: absolute;
      top: 0;
      right: 0;
      top: 10px;
      right: 10px;
      &::before{
        content: url(../assets/icons/close.svg);
        display: block;

      }
    }

    .icon-pencil{
      width: min-content;
      position: absolute;
      bottom: 10px;
      right: 10px;
      &::before{
        content: url(../assets/icons/pencil.svg);
        display: block;

      }
    }
  }


  .add-card{
    position: relative;
    border: 2px dashed #d9d9d9;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 32px;
    box-sizing: border-box;


    .icon-plus{
      width: min-content;
      font-weight: 400;
      opacity: 50%;

      &::before{
        content: url(../assets/icons/plus.svg);
        display: block;
      }
    }
    p{opacity: 50%;}
  }
    
    
    </style>
      