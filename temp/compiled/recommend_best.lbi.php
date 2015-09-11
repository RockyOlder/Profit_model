<?php if ($this->_var['best_goods']): ?>

  <?php $_from = $this->_var['best_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_0_25333800_1435216453');if (count($_from)):
    foreach ($_from AS $this->_var['goods_0_25333800_1435216453']):
?>
 <dd class="hot_dd02" >
  <div class="hot_img"> <span class="hot1"></span>
    
        <div class="pic">   <a href="<?php echo $this->_var['goods_0_25333800_1435216453']['url']; ?>"><img src="<?php echo $this->_var['goods_0_25333800_1435216453']['thumb']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods_0_25333800_1435216453']['name']); ?>" class="goodsimg" /></a></div>
           <div class="text">
          <p class="name"><a href="<?php echo $this->_var['goods_0_25333800_1435216453']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_0_25333800_1435216453']['name']); ?>"><?php echo $this->_var['goods_0_25333800_1435216453']['short_style_name']; ?></a></p>
          <p class="brife"> <?php echo $this->_var['goods_0_25333800_1435216453']['brief']; ?></p>
          
           <p class="price" > <font class="aa"> 
           <?php if ($this->_var['goods_0_25333800_1435216453']['promote_price'] != ""): ?>
          <?php echo $this->_var['goods_0_25333800_1435216453']['promote_price']; ?>
          <?php else: ?>
          <?php echo $this->_var['goods_0_25333800_1435216453']['shop_price']; ?>
          <?php endif; ?>
           </font><font class="bb"><?php echo $this->_var['goods_0_25333800_1435216453']['market_price']; ?></font>
           </p>
        </div>
        </div>
         <div class="hot_btn"><a href="javascript:addToCartShowDiv(<?php echo $this->_var['goods_0_25333800_1435216453']['id']; ?>,1,'best')"  ></a> </div>
      <div class="hot_line" > </div>
      <div class="sucess_joinCart" id="addtocartdialog_retui_<?php echo $this->_var['goods_0_25333800_1435216453']['id']; ?>_best">

</div>
        </dd>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

<?php endif; ?>
