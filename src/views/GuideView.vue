<template>
  <div class="guid">
    <div class="guid-img">
      <div class="cover">
        <p>ご利用ガイド</p>
      </div>
    </div>
    <div class="guid-info">
      <h3>返品、交換について</h3>
      <p>原則、返品、交換はお受けすることが出来ませんが、商品が不良品、
        または注文と異なる場合のみ返品、交換の受付をさせて頂きます。
        なお、その際は商品到着日より7日以内に御連絡下さい。
        商品の性質上、イメージの相違等のお客様ご都合による返品、
        交換はお受け出来ません。</p>
    </div>
    <div class="guid-info">
      <h3>お支払方法について</h3>
      <p>銀行振込及び代金引換郵便(現金、クレジットカード、デビットカード)にてお支払い下さい。</p>
      <p>
        <strong>［銀行振込の場合］</strong><br />
        指定する銀行口座にお振り込み下さい。
      </p>
      <p>
        <strong>［代金引換郵便の場合］</strong>
        ご注文頂きました商品をお受け取り頂く際、商品と引換に、配送業者へ商品代金を現金、クレジットカード、デビットカードにてお支払下さい。
        なおその際、代金引換手数料はお客様にご負担して頂いております。ご了承下さい。
      </p>
      <p>
        <strong>−代金引換手数料（税別）</strong>
        <br />
        1万円未満               -----300円
        <br />
        1万円以上～3万円未満     -----400円
        <br />
        3万円以上～10万円未満    -----600円
        <br />
        10万円以上～30万円まで -----1,000円
        <br />
      </p>
    </div>
    <div style="border:none" class="guid-info">
      <h3 >配送方法について</h3>

      <p>
        ご注文頂きました商品の配送は、ヤマト運輸にて配送させて頂きます。
        商品の配送状況の確認はお問い合わせ伝票番号をお知らせ致しますので、ヤマト運輸に配送状況をご確認下さい。
        配送後の送り先の変更、また指定時間を過ぎても商品が届かない等のお問合せにつきましては、
        お問い合わせ伝票番号をご確認の上、ヤマト運輸に直接ご連絡をお願い致します。
        <br>
        ［ヤマト運輸株式会社サービスセンター ：フリーダイヤル tel:<a style="text-decoration: none; font-weight: 600; color: #1e1e1e;" href="0120-01-9625">0120-01-9625</a>
      </p>
      <p>
        <strong>−送料（税別）</strong>
        <br>
        関西・中部・北陸・中国…600円、
        <br />
        関東・信越・四国・九州…700円、
        <br />
        南東北…800円、北東北…900円、
        <br />
        沖縄…1,2000円、 北海道・その他…1,400円、
        <br>
      </p>

      <p>
        <strong>−配送日時、時間指定について</strong>
        <br>
        配送日時、時間指定は可能ですが、ご指定頂ける到着日や時間帯は、お支払方法によって異なります。
      </p>
      <p><strong>［日時指定］</strong>
        <br />
        銀行振込の場合は入金後、3日後以降の指定
        <br />
        代金引換の場合はご注文日より3日後以降の指定
        <br />
        例）4月1日に銀行振込入金、ないし代金引換にて注文の場合、4月4日以降の指定が可能
        。
      </p>
      <p>
        <strong>［時間指定］</strong>
        <br>
        ※天候や交通状況、または地域により、ご指定の時間にお届け出来ない場合もございます。
        <br>
        午前中/ 12時～14時 / 14時～16時 / 16時～18時 / 18時～20時 / 20時～21時
      </p>
    </div>


  </div>
</template>

<style lang="scss" scoped>
.guid-img {
  background-image: url(../assets/images/guid.jpg);
  background-size: cover;
  background-position: center;
  color: #fff;
  text-align: center;
  height: 450px;
  position: relative;

  .cover {
    position: absolute;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.15);

    p {
      font-weight: 700;
      font-size: 36px;
    }

  }

}
.guid-info {
    border-bottom: 1px solid #ccc;

    h3,p{
      text-align: center;
      word-wrap: break-word;
    }

  }
</style>
