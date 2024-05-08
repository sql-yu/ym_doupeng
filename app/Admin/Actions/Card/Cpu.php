<?php

namespace App\Admin\Actions\Card;

use Dcat\Admin\Widgets\Metrics\SingleRound;

class Cpu  extends SingleRound
{
    private $shiyong = 0;
    private $all = 0;
    private $lv = 0;
    public function __construct($title = null, $icon = null,$data = [])
    {
        $this->shiyong = $data[0];
        $this->all = $data[1];
        $this->lv = $data[2];
        parent::__construct($title, $icon);
    }

    public function init()
    {
        parent::init();
        $this->contentWidth(0, 12);
    }

    public function fill()
    {
        $this->withChart($this->lv);
        $this->withFooter($this->shiyong, $this->all);
    }

    public function render()
    {
        $this->fill();

        return parent::render();
    }

    public function withChart($percent)
    {
        return $this->chart([
            'series' => [$percent],
        ]);
    }

    public function withFooter($completed, $inProgress)
    {
        return $this->footer(
            <<<HTML
<div class="row text-center mx-0" style="width: 100%">
  <div class="col-6 border-top border-right d-flex align-items-between flex-column py-1">
      <p class="mb-50">Used</p>
      <p class="font-lg-1 text-bold-700 mb-50">{$completed}</p>
  </div>
  <div class="col-6 border-top d-flex align-items-between flex-column py-1">
      <p class="mb-50">Total</p>
      <p class="font-lg-1 text-bold-700 mb-50">{$inProgress}</p>
  </div>
</div>
HTML
        );
    }
}
