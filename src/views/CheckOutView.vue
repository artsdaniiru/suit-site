<template>

<div class="checkout-view">

  <h2>チェックアウト</h2>

  <div class="checkout-content">


    <h2>配達</h2>
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

    <div class="order-wrap">
      <h2>お支払方法</h2>
      <div class="card-wrap">
        <p>クレジットカード</p>
        <div class="cardchoise-wrap">
          <input class="radioBtn" type="radio" id="one" value="One" v-model="picked" />
          <label for="Visa 末尾 9696">Visa 末尾 9696</label>
        </div>
        <div class="cardchoise-wrap">
          <input class="radioBtn" type="radio" id="one" value="One" v-model="picked" />
          <label for="Visa 末尾 9696">Visa 末尾 9696</label>
        </div>
        <a class="addcard">
          クレジットカードまたはデビットカードを追加する
        </a>
      </div>


    </div>
  </div>


</div>



</template>
  
<script>

</script>
  
<style lang="scss" scoped>
 h2{
  font-weight: 600;
  font-size: 24px;
  text-align: left;
 }

 .checkout-content{
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  padding: 24px;
 }


 .adress-wrap{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;

 


  .adress-card{
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    position: relative;
    padding: 32px;
    box-sizing: border-box;
    width: 100%;



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


 }

 .order-wrap{


  .cardchoise-wrap{
    display: flex;
    align-items: baseline;
    gap: 50px;

    .radioBtn{
      position: relative;
      display: block;
      height: min-content;
      margin-bottom: 24px;
      &::before{
        content: url(../assets/icons/visa.svg);
        display: block;
        position: absolute;
        left: 20px;
        bottom: -4px;
        height: 21px;
      }
    }
  }
  .addcard{
    padding-left: 30px;
    position: relative;
    &::before{
        content: url(../assets/icons/plus.svg);
        display: block;
        position: absolute;
        transform: scale(0.5); 
        bottom: -10px;
      }

  }
 }

  








</style>
  