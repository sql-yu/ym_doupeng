<?php

namespace App\Admin\Actions\Card;

use Dcat\Admin\Widgets\Metrics\RadialBar;

class Tickets extends RadialBar
{
    /**
     * 初始化卡片内容
     */
    public function init()
    {
        parent::init();

        $this->title('内存');
        $this->height(300);
        $this->chartHeight(300);
        $this->chartLabels('Completed Tickets');
        $this->withContent('');
        $this->withChart(83);
        $this->withFooter(1,2,3);
    }

    /**
     * 设置图表数据.
     *
     * @param int $data
     *
     * @return $this
     */
    public function withChart(int $data)
    {
        return $this->chart([
            'series' => [$data],
        ]);
    }

    /**
     * 卡片内容
     *
     * @param string $content
     *
     * @return $this
     */
    public function withContent($content)
    {
        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    <h1 class="font-lg-2 mt-2 mb-0">{$content}</h1>
    <small>RAM</small>
</div>
HTML
        );
    }

    /**
     * 卡片底部内容.
     *
     * @param string $new
     * @param string $open
     * @param string $response
     *
     * @return $this
     */
    public function withFooter($new, $open, $response)
    {
        return $this->footer(
            <<<HTML
<div class="d-flex justify-content-between p-1" style="padding-top: 0!important;">
    <div class="text-center">
        <p>New Tickets</p>
        <span class="font-lg-1">{$new}</span>
    </div>
    <div class="text-center">
        <p>Open Tickets</p>
        <span class="font-lg-1">{$open}</span>
    </div>
</div>
HTML
        );
    }
}
