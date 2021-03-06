<?php
require_once __DIR__ . '/../../../lib/Treffynnon/At/Wrapper.php';
use Treffynnon\At\Wrapper as At;

class AtWrapperTest extends \PHPUnit_Framework_TestCase {
    protected $test_file = '';
    public function setUp() {
        $this->test_file = tempnam(sys_get_temp_dir(), 'php');
    }
    public function testAtCmd() {
        $job = 'echo "hello" | wall';
        $time = 'now + 1min';
        $obj = At::cmd($job, $time);
        $this->assertInstanceOf('Treffynnon\At\Job', $obj);
        $this->cleanUpJobs($obj);
    }

    public function testAtFile() {
        $file = $this->test_file;
        $time = 'now + 1min';
        $obj = At::file($file, $time);
        $this->assertInstanceOf('Treffynnon\At\Job', $obj);
        $this->cleanUpJobs($obj);
    }
    
    public function testAtLq() {
        $this->setDependencies(array('testAtFile','testAtCmd'));
        $job = 'echo "hello" | wall';
        $time = 'now + 1min';
        At::cmd($job, $time, 't');
        At::cmd($job, $time, 't');
        $array = At::lq('t');
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $array);
        $this->assertGreaterThanOrEqual(2, count($array));
        $this->cleanUpJobs($array);
    }

    public function testRegressionIssue2UsernameRegexDoesntSupportHyphens() {
        $regex = TestableAtWrapper::getQueueRegex();
        $test_strings = array(
            '17      Mon Nov 15 10:55:00 2010 a simon',
            '18      Mon Nov 15 10:55:00 2010 a simons-username',
            '2       2010-11-15 10:53 a root',
            '3       2010-11-15 10:54 a root-username-',
        );
        $m = 0;
        foreach($test_strings as $test) {
            $m += preg_match($regex, $test);
        }
        $this->assertSame($m, count($test_strings));
    }
    
    public function tearDown() {
        unlink($this->test_file);
    }
    
    private function cleanUpJobs($jobs) {
        if(!is_array($jobs)) {
            $jobs = array($jobs);
        }
        foreach($jobs as $job) {
            try {
                $job->rem();
            } catch(JobNotFoundException $e) {
                
            }
        }
    }
}

class TestableAtWrapper extends At {
    public static function getQueueRegex() {
        return static::$queueRegex;
    }
}
