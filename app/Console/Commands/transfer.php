<?php

namespace App\Console\Commands;
use App\Models\Game;
use Illuminate\Console\Command;
use Google\Cloud\Translate\V2\TranslateClient;
class transfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    
    protected $translate;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->translate = new TranslateClient(['key' => 'AIzaSyAA4EX2EOq8jaUp4dklzgQgrbiuTzRFm0s']);
        //description_contents,instruction
        $count = Game::count();
        $i = (int)($count/100);
        for($j=0;$j<=$i;$j++){
            $list = Game::skip($j*100)->take(100)->get();
            foreach($list as $v){
                try{
                    if($v->description_contents){
                        $v->description_contents =$this->transferInfo($v->description_contents);
                    }
                    if($v->instruction){
                        $v->instruction =$this->transferInfo($v->instruction);
                    }
                   
                    $v->save();
                }catch(\Exception $e){
                    echo $e->getMessage()."\n";
                    echo $v->id."\n";
                }
                sleep(1);
            }
            sleep(1);
            echo "循环：".$j."\n";
        }

        return Command::SUCCESS;
    }
    
    public function transferInfo($data){
        $result = $this->translate->translate($data, ['target' => 'pt-BR']);
        return $result['text'];
    }
}
