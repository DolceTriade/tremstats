    <div id="footer">
      <span>
        Output calculated in
        <?php
        $calculation_end = microtime(true);
        $calculation_time = $calculation_end - $this->calculation_start;
        
        echo round($calculation_time, 4);
        ?>
        seconds
      </span>
      Tremstats v<?php echo VERSION; ?> by <a href="http://www.dasprids.de" title="DASPRiD's">DASPRiD</a> ~ <a href="http://www.capponcino.it/alessio/" title="Slux's">Slux Mod</a> ~ Rezyn
    </div>
  </body>
</html>
