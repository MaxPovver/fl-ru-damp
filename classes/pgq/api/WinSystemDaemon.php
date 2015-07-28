<?php
define("PIDFILE_PREFIX", "tmp");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pgq/api/SimpleLogger.php");

declare(ticks = 1);

/**
 * SystemDaemon для Windows.
 * Команды stop и kill действуют одинаково, т.е. устанавливаем daemon->killed в TRUE и выходим.
 */

// WIN ////////////////////////////////////////////
$sigh_handler = NULL;
$sigh_object  = NULL;
function pcntl_signal($sig, $handler) {
  global $sigh_method, $sigh_object;
  list($sigh_object, $sigh_method) = $handler;
}
function posix_kill($pid, $sig) {
  global $sigh_method, $sigh_object;
  $sigh_object->$sigh_method($sig);
}
function posix_get_last_error() {return;}
function posix_strerror() {return 'windows error';}
// WIN ////////////////////////////////////////////



abstract class SystemDaemon
{
  protected $loglevel = DEBUG;
  protected $logfile;
  protected $delay = 15;
  protected $log;
  protected $commands = array("start", "stop", "kill", "restart",
			      "status", "reload",
			      "logmore", "logless");
	
  protected $name;
  protected $fullname;

  protected $pidfile;
  protected $sid;	


// WIN ////////////////////////////////////////////
  protected $php_process_name = 'php.exe';
  protected $hupfile;
  protected $is_daemon = false;

  protected function __get($nm) {
    if($nm=='killed') return $this->killed();
    if($nm=='huped')  return $this->huped();
  }

  protected function killed($value = NULL)
  {
    if($value === NULL)
      return !$this->getpid(true);

    if($value === true) {
      $this->log->notice("Set daemon->killed to true...");
      $this->droppidfile();
    }
  }

  protected function huped($value = NULL)
  {
    if($value === NULL)
      return file_exists($this->hupfile);
    
    if($value === true) {
      $this->log->notice("Set daemon->huped to true...");
      if(!($hupd = fopen($this->hupfile,'w'))) {
        $this->log->error("Hupfile fopen('%s') failed: %s", 
        $this->hupfile, 
        posix_strerror(posix_get_last_error()));
      }
      else
        fclose($hupd);
    }
    else if($value === false) {
      if( file_exists($this->hupfile) ) {
        if( ! unlink($this->hupfile) )
          $this->log->error("Cound not unlink '%s'", $this->hupfile);
      }
      else
        $this->log->error("Hupfile '%s' does not exist", $this->hupfile);
    }
  }

  protected function wincheckpid($pid)
  {
    exec('tasklist /fo table', $prcs);
    foreach($prcs as $prc) {
      $p = preg_split('/\s+/',$prc);
      if($p[1] == $pid && !strcasecmp($p[0],$this->php_process_name))
        return true;
    }
    return false;
  }

  private function initlog()
  {
    /**
     * config() provides log filename and loglevel
     */
    $this->config();
    //unset( $this->log );
    $this->log = new SimpleLogger($this->loglevel, $this->logfile, $this->is_daemon);
    if( ! $this->log->check() ) {
      fprintf(STDERR, "FATAL: could not open logfile '%s': %s",
      $this->logfile,
      posix_strerror(posix_get_last_error()));
      exit;
    }     
    $this->log->notice("Init done (config & logger)");
  }
// WIN ////////////////////////////////////////////


  public function __construct( $argc, &$argv)
  {		
    $this->fullname = $argv[0];
    $this->name     = basename($this->fullname);
    $this->pidfile  = sprintf("%s/%s.pid", PIDFILE_PREFIX, $this->name);

    $this->log = new SimpleLogger(WARNING, STDOUT);
    $this->main($argc, $argv);
  }

  /**
   * Implement those functions when inheriting from this class.
   */
  protected function config() {	}
  protected function process() {	}
  protected function php_error_hook() { }
  
  /**
   * main is responsible of command line parsing and daemon interactions
   */
// WIN ////////////////////////////////////////////
  public function main($argc, $argv)
  {
    $this->is_daemon = ($argv[1] == 'start' && $argv[2] == 'daemon'); // сам демон запущен, действо будет происходить в текущем процессе.

    if( $argc != 2 && !$this->is_daemon ) {
      fprintf(STDERR, $this->usage($this->name));
      exit(1);
    }

    if(!file_exists(PIDFILE_PREFIX))
      mkdir(PIDFILE_PREFIX,0777);

    $this->hupfile = sprintf("%s/%s.hup", PIDFILE_PREFIX, $this->name);
    pcntl_signal(SIGTERM, array(&$this, "handleSignals"));
		
    switch( $argv[1] ) {
      case "start":
        $pid = $this->getpid();
        if( $pid !== false ) {
          printf("Trying to start already running daemon '%s' [%s] \n", $this->name, $pid);
          exit(4);
        }
        $this->initlog();
        $this->start();
        break;
				
      case "stop":
        $pid = $this->checkpid(4);
        $this->initlog();
        posix_kill($pid, SIGINT);
        break;
      
      case "kill":
        $pid = $this->checkpid(4);
        $this->initlog();
        posix_kill($pid, SIGTERM);
        break;

      case "restart":
        $pid = $this->checkpid(4);
        $this->initlog();
        if($pid) {
          posix_kill($pid, SIGINT);
          while($this->getpid(true) || $this->wincheckpid($pid))
            sleep(1);
        }
        $this->start();
        break;
      
      case "status":
        $this->status();
        break;
      
      case "reload":
        $pid = $this->checkpid(4);
        $this->initlog();
        posix_kill($pid, SIGHUP);
        break;
      
      case "logmore":
        $pid = $this->checkpid(4);
        $this->initlog();
        posix_kill($pid, SIGUSR1);
        break;
      
      case "logless":
        $pid = $this->checkpid(4);
        $this->initlog();
        posix_kill($pid, SIGUSR2);
        break;
      
      default:
        printf($this->usage($this->name));
        exit(1);
        break;        
    }
  }
  
  /**
   * start the daemon
   */
  public function start()
  {
    /**
     * Windows daemon startup.
     */
    
    if(!$this->is_daemon) {
      $handle = popen("start /b {$this->php_process_name} \"{$this->fullname}\" start daemon", 'r');
      pclose($handle);
      $this->log->notice("Starting daemon %s & exit", $this->fullname);
      exit;
    }

    // Redefine PHP language error handlers
    set_error_handler( array( $this, "phpFault" ) );
    set_exception_handler( array( $this, "exceptFault" ) );
			 
    $this->createpidfile(); 
		
    /**
     * Now we're ready to run.
     */
    $this->run();
  }
// WIN ////////////////////////////////////////////
	

  /**
   * At quitting time, drop the pidfile and write to the logs we're done.
   */
  public function stop() 
  {
    $this->droppidfile();
    $this->log->debug("Quitting...");
    exit(0);
  }
	
  
  /**
   * status will simply print out if daemon is running, and under which pid.
   */
  public function status()
  {
    $pid = $this->getpid();
    if( $pid === False )
      printf("SystemDaemon %s is not running.\n", $this->name);
    else {
      printf("SystemDaemon %s is running with pid %d\n", $this->name, $pid);
    }
  }
	
  /**
   * Print out the supported commands.
   */
  public function usage($progname) 
  {
    return sprintf("%s: %s\n", $progname, implode("|", $this->commands));
  }
	
  /**
   * checkpid() will call getpid() and exit with the given error code
   * when the daemon is not running.
   */
  public function checkpid($errcode) {
    $pid = $this->getpid();
    
    if( $pid === false ) {
      fprintf(STDERR, "No daemon '%s' running \n", $this->name);
      exit($errcode);
    }    
    return $pid;
  }
	
  /**
   * getpid() ensure that the daemon is running, returning its pid
   * when it's the case and False when it's no more running.
   */
// WIN ////////////////////////////////////////////
  public function getpid($fast_check = false) {
    if( file_exists($this->pidfile) )
    {    
      if($fast_check) {
        // может еще пару быстрых проверок...
        return true;
      }

      if(($pid = file_get_contents($this->pidfile))
         && $this->wincheckpid($pid))
        return $pid;
			
      printf("pidfile: %s does not match '%s' \n",
       $pid, $this->fullname);
      $this->droppidfile();
    }
    return false;
  }
// WIN ////////////////////////////////////////////



	
  /**
   * startup time utility to write our pid to pidfile.
   *
   * We check for stale pidfile and remove it if necessary.
   */
  public function createpidfile() {
    if( file_exists($this->pidfile) ) {
      $this->log->error("Pidfile '%s' already exists", $this->pidfile);
      $this->droppidfile();
    }			
    $fd = fopen($this->pidfile, "w+");
    
    if( $fd !== false ) {
      if( fwrite($fd, getmypid()) === False ) {
        $this->log->fatal("Pidfile fwrite('%s') failed: %s", 
			  $this->pidfile, 
			  posix_strerror(posix_get_last_error()));
        $this->droppidfile();
        exit(2); 
      }

      if( fclose($fd) === False ) {
        $this->log->fatal("Pidfile fclose('%s') failed: %s", 
			  $this->pidfile,
			  posix_strerror(posix_get_last_error()));
        $this->droppidfile();
        exit(2);     
      }
    }
    else {
      $this->log->fatal("Pidfile fopen('%s') failed: %s", 
			$this->pidfile,
			posix_strerror(posix_get_last_error()));
      exit(2);
    }
    $this->log->notice("Pidfile '%s' created with '%s'",
		       $this->pidfile, getmypid());
  }


  /**
   * drop our pidfile
   */	
  public function droppidfile()
  { 
// WIN ////////////////////////////////////////////
    if($this->is_daemon && !file_exists($this->pidfile))
      return;
// WIN ////////////////////////////////////////////
    
    $this->log->notice("rm %s", $this->pidfile);

    if( file_exists($this->pidfile) ) {
      if( ! unlink($this->pidfile) )
        $this->log->error("Cound not unlink '%s'", $this->pidfile);
    }
    else
      $this->log->error("Pidfile '%s' does not exist", $this->pidfile);
  }
	
  /**
   * The run() function leads the daemon work, by calling user
   * function process() and sleeping $this->delay, as long as we
   * didn't get INT or TERM signal.
   */
  public function run()
  {
    $this->log->notice("run");

    while( ! $this->killed )
    {
      if( $this->huped ) {
        $this->config();
	  
        // Don't forget to forward the loglevel change if any
        if( $this->loglevel )
          $this->log->loglevel = $this->loglevel;
	  
        // And to force logfile reopening (be nice to log rotating)
        $this->log->reopen();
	  
// WIN ////////////////////////////////////////////
        $this->huped(false);
// WIN ////////////////////////////////////////////
      }
	
      if(!DEBUG_DAEMON)
        $this->process();      
	
      if( ! $this->killed ) {
        $this->log->debug("sleeping %d seconds", $this->delay);
        sleep($this->delay);
      }

    }
    $this->stop();
  }



  /**
   * React to supported user signals
   */
  public function handleSignals($sig) {
    switch($sig) {
// WIN ////////////////////////////////////////////
      case SIGTERM:
        $this->log->warning("Received TERM signal.");
        $this->killed(true);
        break;

      case SIGINT:
        $this->log->warning("Received INT signal.");
        $this->killed(true);
        break;
				
      case SIGHUP:
        $this->log->warning("Received HUP signal");
        $this->huped(true);
        break;
// WIN ////////////////////////////////////////////
				
      case SIGUSR1:
        $this->log->warning("Received USR1 signal, logging more");
        $this->log->logmore();
        break;
				
      case SIGUSR2:
        $this->log->warning("Received USR1 signal, logging less");
        $this->log->logless();
        break;
    }
  }
		
  /**
   * Register our own PHP language error handlers
   */
  function phpFault( $errno, $errstr, $errfile, $errline )
  {
    $message = "PHP: ".strip_tags($errstr)." in {$errfile}:{$errline}";

    switch( (int)$errno )
    {
      case E_STRICT:
      case E_PARSE:    
      case E_CORE_ERROR: 
      case E_CORE_WARNING:
      case E_COMPILE_ERROR: 
      case E_COMPILE_WARNING:
        $this->log->fatal( $message );
        $this->stop();
        break;

      case E_USER_ERROR:
      case E_ERROR:
        $this->log->error( $message );
        $this->php_error_hook();
        break;
	
      case E_WARNING:
      case E_USER_WARNING:
        //case E_RECOVERABLE_ERROR:
        $this->log->warning( $message );
        return true;
        break;
	
      case E_NOTICE:
      case E_USER_NOTICE:
        $this->log->notice( $message );
        return true;
        break;
    }
    return false;
  }

  /**
   * We also support non-catched exceptions and consider them as fatal
   * errors.
   */
  function exceptFault( $exception )
  {
    $trace = $exception->getTrace();
    $message = $exception->getMessage();
    
    if( is_array($trace) && count($trace) > 0 ) {
      $message .= "; source: " . $trace[0]["file"] . ":" . $trace[0]["line"];
    }
    
    $this->log->fatal( $exception->getMessage($message) );
    $this->stop();
  }
}
?>
