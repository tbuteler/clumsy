<?php namespace Clumsy\CMS\Console;

use Illuminate\Foundation\AssetPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Publish the Debugbar assets to the public directory
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */
class PublishCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clumsy:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Clumsy and dependencies\' assets';

    /**
     * The asset publisher instance.
     *
     * @var \Illuminate\Foundation\AssetPublisher
     */
    protected $assets;


    /**
     * Create a new Publish command
     *
     * @param \Illuminate\Foundation\AssetPublisher $assets
     */
    public function __construct(AssetPublisher $assets)
    {
        parent::__construct();

        $this->assets = $assets;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $packages = array(
            'clumsy/utils',
            'clumsy/eminem',
        );

        foreach ($packages as $package)
        {
            if (is_dir($path = $this->vendorPath().'/'.$package.'/public'))
            {
                $this->assets->publish($package, $path);
                $this->info('Assets published for package: '.$package);
            }
            else
            {
                $this->error('Could not find path for: '.$package);
            }
        }

        Artisan::call('asset:publish', array('clumsy/cms'));
        $this->info('Clumsy assets published');
    }

    protected function vendorPath(){
        return base_path().'/vendor';
    }

}
