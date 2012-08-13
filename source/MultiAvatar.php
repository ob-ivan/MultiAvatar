<?php

// config //

define ('DIR_AVATARS', 'avatars');
define ('CACHE_FILENAME', '.filelist');

// error handling //

function exception_error_handler ($errno, $errstr, $errfile, $errline)
{
    throw new ErrorException ($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler('exception_error_handler');

// Entry //

interface EntryInterface
{
    public function __construct ($filepath, $extension);
    public function printHeaders ();
    public function printBody ();
}

class Entry implements EntryInterface
{
    // const //
    
    static protected $ACCEPT_EXTENSIONS = array ('png', 'jpg', 'jpeg', 'gif');
    
    // var //
    
    protected $filepath;
    protected $extension;
    
    // public //
    
    public function __construct ($filepath, $extension)
    {
        if (! file_exists ($filepath))
        {
            throw new EntryException ('File ' . $filepath . ' does not exist', EntryException::FILE_DOES_NOT_EXIST);
        }
        
        if (! in_array ($extension, self::$ACCEPT_EXTENSIONS))
        {
            throw new EntryException ('Extension ' . $extension . ' is not accepted', EntryException::EXTENSION_IS_NOT_ACCEPTED);
        }
        
        $this->filepath = $filepath;
        $this->extension = $extension === 'jpg' ? 'jpeg' : $extension;
    }
    
    public function printHeaders ()
    {
        header ('Content-Transfer-Encoding: binary');
        header ('Content-Type: image/' . $this->extension);
        header ('Content-Length: ' . filesize ($this->filepath));
    }
    
    public function printBody ()
    {
        ob_clean();
        flush();
        readfile ($this->filepath);
    }
}

class EntryException extends Exception
{
    const FILE_DOES_NOT_EXIST       = __LINE__;
    const EXTENSION_IS_NOT_ACCEPTED = __LINE__;
}

// MultiAvatar //

interface MultiAvatarInterface
{
    public function __construct  ($dirName, $cacheFilename);
    public function getEntry ();
}

class MultiAvatar implements MultiAvatarInterface
{
    // var //
    
    protected $dirPath;
    protected $cacheFilename;
    protected $cacheFilepath;
    protected $isReset = false;
    
    // public //
    
    public function __construct  ($dirName, $cacheFilename)
    {
        $this->dirPath = dirname(__FILE__) . '/' . $dirName;
        $this->cacheFilename = $cacheFilename;
        $this->cacheFilepath = $this->dirPath . '/' . $cacheFilepath;
        $this->isReset = isset ($_POST['reset_filelist']) && intval ($_POST['reset_filelist']) === 1;
        
        if (! is_readable ($this->dirPath))
        {
            throw new MultiAvatarException ('Directory ' . $this->dirPath . ' is not readable', MultiAvatarException::DIRECTORY_IS_NOT_READABLE);
        }
        
        if ($this->isReset)
        {
            $this->resetCache();
        }
    }
    
    public function getEntry ()
    {
        $entries = $this->getEntries();
        if (empty ($entries))
        {
            $this->resetCache();
            $entries = $this->getEntries();
            if (empty ($entries))
            {
                throw new MultiAvatarException ('Entry list is empty', MultiAvatarException::ENTRY_LIST_IS_EMPTY);
            }
        }
        shuffle ($entries);
        return array_shift ($entries);
    }
    
    // protected //
    
    protected function getEntries ()
    {
        return unserialize (file_get_contents ($this->cacheFilepath));
    }
    
    protected function resetCache ()
    {
        $dir = dir ($this->dirPath);
        $entries = array ();
        $bad = array ('.', '..', $this->cacheFilename);
        while (true)
        {
            $filename = $dir->read();
            if ($filename === false)
            {
                break;
            }
            
            if (in_array ($filename, $bad))
            {
                continue;
            }
            $extension = preg_replace ('/^.*\.([a-z]{3,4})$/i', '$1', $filename);
            try
            {
                $entries[] = new Entry ($filename, $extension);
            }
            catch (EntryException $e)
            {
                // TODO: Write to log.
            }
        }
        $dir->close();
        if (empty ($entries))
        {
            throw new MultiAvatarException ('Directory ' . $this->dirPath . ' is empty', MultiAvatarException::DIRECTORY_IS_EMPTY);
        }
        file_put_contents ($this->cacheFilepath, serialize ($entries));
    }
}

class MultiAvatarException extends Exception
{
    const DIRECTORY_IS_NOT_READABLE = __LINE__;
    const DIRECTORY_IS_EMPTY        = __LINE__;
    const ENTRY_LIST_IS_EMPTY       = __LINE__;
}

// body //

try
{
    $multiAvatar = new MultiAvatar (DIR_AVATARS, CACHE_FILENAME);
    $entry = $multiAvatar->getEntry();
    $entry->printHeaders();
    $entry->printBody();
}
catch (Exception $e)
{
    print get_class ($e) . ' (' . $e->getCode() . '): ' . $e->getMessage();
}
die;
