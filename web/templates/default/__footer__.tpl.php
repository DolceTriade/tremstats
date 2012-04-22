    <div id="footer">
      <span id="footer_time">
        ( output in
        <?php
        $calculation_end = microtime(true);
        $calculation_time = $calculation_end - $this->calculation_start;
        
        echo round($calculation_time, 3);
        ?>
        seconds )
      </span>
      <span id="footer_release"><a href="https://github.com/ppetr/tremstats">Tremstats Too v<?php echo VERSION; ?></a></span><br>
      <span id="footer_credits"> modified by <a href="https://github.com/ppetr">Petr</a> ~ original by <a href="http://rezyn.mercenariesguild.net">Rezyn</a> ~ and <a href="http://www.dasprids.de" title="DASPRiD's">DASPRiD</a> ~ </span>
    </div>
  </body>
</html>
