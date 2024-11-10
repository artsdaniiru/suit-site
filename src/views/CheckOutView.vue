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

      <div class="add-card">
        <span class="icon-plus"></span>
        <p>新しいお届け先住所を追加する</p>


      </div>

    </div>

    <div class="order-wrap">

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



 .adress-wrap{
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  padding: 24px;




  .adress-card{
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 32px;
    position: 100%;



    .icon-close{
      width: min-content;
      position: absolute;
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
    .icon-plus{
      width: min-content;
      font-weight: 400;
      color: #ec221f;
      position: relative;
      &::before{
        content: url(../assets/icons/plus.svg);
        display: block;
        position: absolute;
        right: 50px;
        opacity: 50%;
      }
      }
  }


 }

  
</style>
  