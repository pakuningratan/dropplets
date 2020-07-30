<?php
if (file_exists("config.php")) {
    include 'config.php';
} else {
    $blogName = null;
    $blogAuthor = null;
    $blogFooter = null;
    $blogPassword = null;
}
// Create the required .htaccess if it dosen't already exist.
if (!file_exists(".htaccess")) {
    $htaccess = fopen(".htaccess", 'w') or die("Unable to set up needed files! Please make sure index.php has write permissions and that the folder it is in has write permissions. This is usually 755.");
    $htaccess_content = "<IfModule mod_rewrite.c>\n\tRewriteEngine On\n\tRewriteCond %{REQUEST_FILENAME} !-f\n\tRewriteCond %{REQUEST_FILENAME} !-d\n\tRewriteCond %{REQUEST_FILENAME} !-l\n\tRewriteRule ^(.*)$ index.php?/$1 [L,QSA]\n</IfModule>";
    fwrite($htaccess, $htaccess_content);
    fclose($htaccess);
}
if (!file_exists("posts")) {
    mkdir("posts");
}
$Extra = new ParsedownExtra();

// Get the url parameters.
$URI = parse_url($_SERVER['REQUEST_URI']);
$URI_parts = explode('/', $URI['path']);

$parts = explode('/', $_SERVER['REQUEST_URI']);
$dir = $_SERVER['SERVER_NAME'];
for ($i = 0; $i < count($parts) - 1; $i++) {
    $dir .= $parts[$i] . "/";
}
function shapeSpace_check_https()
{
    return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
}
if (shapeSpace_check_https()) {
    $SITE_HOME = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
} else {
    $SITE_HOME = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
}
$SITE_HOME = str_replace("/index.php", "", $SITE_HOME);

$URI_parts = array_reverse(explode('/', rtrim($URI['path'], '/')));
// If a form is submitted, process it. Otherwise, show the main web page.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Setup form submitted, create config.php.
    if (test_input($_POST["form"]) == "setup") {
        // Check that the form items are there.
        if (isset($_POST["blogName"]) and isset($_POST["blogAuthor"]) and isset($_POST["blogPassword"]) and isset($_POST["blogStyleType"])) {
            // Get the stylesheet link (placed here to be used in both the if config.php and if not)
            if (test_input($_POST["blogStyleType"]) == "default") {
                $blogStyleSheet = "https://raw.githack.com/johnroper100/dropplets/master/main.css";
                $blogPostStyleSheet = "https://raw.githack.com/johnroper100/dropplets/master/main.css";
            } else if (test_input($_POST["blogStyleType"]) == "zen") {
                $blogStyleSheet = "https://raw.githack.com/johnroper100/dropplets/master/zenStyle.css";
                $blogPostStyleSheet = "https://raw.githack.com/johnroper100/dropplets/master/zenStyle.css";
            } else {
                $blogStyleSheet = test_input($_POST["blogStyleSheet"]);
                $blogPostStyleSheet = test_input($_POST["blogPostStyleSheet"]);
            }
            // If the config does not exist, create it. If it does, update it.
            if (!file_exists("config.php")) {
                $password_hash = password_hash(test_input($_POST["blogPassword"]), PASSWORD_BCRYPT);

                $config_content = "<?php\n\$blogName='" . test_input($_POST["blogName"]) . "';\n\$blogAuthor='" . test_input($_POST["blogAuthor"]) . "';\n\$blogFooter='" . test_input($_POST["blogFooter"]) . "';\n\$blogPassword='" . $password_hash . "';\n\$blogStyleType='" . test_input($_POST["blogStyleType"]) . "';\n\$blogStyleSheet='" . $blogStyleSheet . "';\n\$blogPostStyleSheet='" . $blogPostStyleSheet . "';\n\$headerInject='';\n\$footerInject='';\n?>";
            } else {
                if (password_verify(test_input($_POST["blogPassword"]), $blogPassword)) {
                    $config_content =
                        "<?php\n\$blogName='" . test_input($_POST["blogName"]) . "';\n\$blogAuthor='" . test_input($_POST["blogAuthor"]) . "';\n\$blogFooter='" . test_input($_POST["blogFooter"]) . "';\n\$blogPassword='" . $blogPassword . "';\n\$blogStyleType='" . test_input($_POST["blogStyleType"]) . "';\n\$blogStyleSheet='" . $blogStyleSheet . "';\n\$blogPostStyleSheet='" . $blogPostStyleSheet . "';\n\$headerInject='" . $headerInject . "';\n\$footerInject='" . $footerInject . "';\n?>";
                } else {
                    echo ("Management password not correct!");
                    exit;
                }
            }
        }
        $config = fopen("config.php", 'w') or die("Unable to set up needed files! Please make sure index.php has write
permissions and that the folder it is in has write permissions. This is usally 755.");
        fwrite($config, $config_content);
        fclose($config);
        header("Location: " . dirname($_SERVER['REQUEST_URI']));
    } else if (test_input($_POST["form"]) == "post") {
        if (file_exists("config.php")) {
            if (
                isset($_POST["blogPostTitle"]) and isset($_POST["blogPostContent"]) and isset($_POST["blogPassword"])
            ) {
                if (password_verify(test_input($_POST["blogPassword"]), $blogPassword)) {
                    $post_content =
                        "<?php\n\$postTitle='" . test_input($_POST["blogPostTitle"]) . "';\n\$postContent='" . test_input($_POST["blogPostContent"]) . "';\n\$postDate=" . time() . ";\n?>";

                    if (!file_exists("posts/" . create_slug(urlencode(test_input($_POST["blogPostTitle"]))) . ".php")) {
                        $result = create_slug(urlencode(test_input($_POST["blogPostTitle"]))) . ".php";
                    } else {
                        header("Location: /post");
                    }
                    $post = fopen("posts/" . $result, 'w') or die("Unable to set up needed files!
Please make sure index.php has write
permissions and that the folder it is in has write permissions. This is usually 755.");
                    fwrite($post, $post_content);
                    fclose($post);
                    header("Location: " . dirname($_SERVER['REQUEST_URI']));
                } else {
                    echo ("Management password not correct!");
                    exit;
                }
            }
        } else {
            header("Location: setup");
        }
    } else if (test_input($_POST["form"]) == "postUpload") {
        if (file_exists("config.php")) {
            if (
                isset($_POST["blogPostTitle"]) and isset($_POST["blogPostFile"]) and isset($_POST["blogPassword"])
            ) {
                if (password_verify(test_input($_POST["blogPassword"]), $blogPassword)) {
                    $post_content =
                        "<?php\n\$postTitle='" . test_input($_POST["blogPostTitle"]) . "';\n\$postContent='" . test_input($_POST["blogPostFile"]) . "';\n\$postDate=" . time() . ";\n?>";

                    if (!file_exists("posts/" . create_slug(urlencode(test_input($_POST["blogPostTitle"]))) . ".php")) {
                        $result = create_slug(urlencode(test_input($_POST["blogPostTitle"]))) . ".php";
                    } else {
                        header("Location: /post");
                    }
                    $post = fopen("posts/" . $result, 'w') or die("Unable to set up needed files!
Please make sure index.php has write
permissions and that the folder it is in has write permissions. This is usually 755.");
                    fwrite($post, $post_content);
                    fclose($post);
                    header("Location: " . dirname($_SERVER['REQUEST_URI']));
                } else {
                    echo ("Management password not correct!");
                    exit;
                }
            }
        } else {
            header("Location: setup");
        }
    } else if (test_input($_POST["form"]) == "update") {
        if (file_exists("config.php")) {
            if (isset($_POST["blogPassword"])) {
                if (password_verify(test_input($_POST["blogPassword"]), $blogPassword)) {
                    file_put_contents("index.php", fopen("https://raw.githack.com/johnroper100/dropplets/master/index.php", 'r'));
                    header("Location: " . dirname($_SERVER['REQUEST_URI']));
                } else {
                    echo ("Management password not correct!");
                    exit;
                }
            }
        } else {
            header("Location: setup");
        }
    } else {
        echo ("The form could not be submitted. Please try again later.");
    }
} else {
    // If the url is setup, check for config and then show the setup page.
    if (count($URI_parts) >= 1 and $URI_parts[0] and $URI_parts[0] == 'setup') {
        if (!isset($blogStyleType)) {
            $blogStyleType = 'default';
        }
?>
        <!DOCTYPE html>
        <html>

        <head>
            <?php if (empty($blogName)) { ?>
                <title>Dropplets | Setup</title>
            <?php } else { ?>
                <title><?php echo ($blogName); ?> | Setup</title>
            <?php } ?>
            <link type="text/css" rel="stylesheet" href="https://meyerweb.com/eric/tools/css/reset/reset.css" />
            <link type="text/css" rel="stylesheet" href="https://raw.githack.com/johnroper100/dropplets/master/setup.css" />
        </head>

        <body>
            <main>
                <div class="setupHeader">
                    <a href="https://github.com/johnroper100/dropplets"><span class="headerLogo"></span><span class="droppletsName">Dropplets</span></a>
                </div>
                <?php if (empty($blogName)) { ?>
                    <h1>Let's create your blog</h1>
                <?php } else { ?>
                    <h1>Edit your blog's settings</h1>
                <?php } ?>
                <form method="post" action="setup">
                    <fieldset>
                        <legend>First, some details:</legend>
                        <input type="text" name="blogName" placeholder="Enter your blog's name" required value="<?php echo ($blogName); ?>" />
                        <input type="text" name="blogAuthor" placeholder="Enter your blog's default author" required value="<?php echo ($blogAuthor); ?>" />
                        <input type="text" name="blogFooter" placeholder="Enter an optional footer message" value="<?php echo ($blogFooter); ?>" />
                    </fieldset>

                    <fieldset>
                        <legend>Second step, choose your stylesheet:</legend>
                        <select name="blogStyleType" onchange="showStyleInput(this);">
                            <option value="default" <?php if ($blogStyleType == 'default') { ?>selected<?php } ?>>Use the
                                default one</option>
                            <!--<option value="zen" <?php if ($blogStyleType == 'zen') { ?>selected<?php } ?>>Use the zen one</option>-->
                            <option value="custom" <?php if ($blogStyleType == 'custom') { ?>selected<?php } ?>>Use your own stylesheet</option>
                        </select>
                        <input id="blogStyleSheet" type="url" name="blogStyleSheet" placeholder="URL to your homepage stylesheet" value="<?php if ($blogStyleType == 'custom') {
                                                                                                                                                echo ($blogStyleSheet);
                                                                                                                                            }  ?>" />
                        <input id="blogPostStyleSheet" type="url" name="blogPostStyleSheet" placeholder="URL to your post stylesheet" value="<?php if ($blogStyleType == 'custom') {
                                                                                                                                                    echo ($blogPostStyleSheet);
                                                                                                                                                } ?>" />
                    </fieldset>
                    <fieldset>
                        <?php if (empty($blogName)) { ?>
                            <legend>Last but not least, the password:</legend>
                            <input type="password" name="blogPassword" placeholder="Choose a good password" required />
                        <?php } else { ?>
                            <legend>Type your password to update your blog:</legend>
                            <input type="password" name="blogPassword" placeholder="Management password" required />
                        <?php } ?>
                    </fieldset>
                    <input type="hidden" name="form" value="setup" required />
                    <?php if (empty($blogName)) { ?>
                        <input class="btn" type="submit" value="Create Your Blog" />
                    <?php } else { ?>
                        <input class="btn" type="submit" value="Update Your Blog" />
                    <?php } ?>
                </form>
            </main>
            <script>
                <?php if ($blogStyleType == 'default' or $blogStyleType == 'zen') { ?>
                    document.getElementById("blogStyleSheet").style.display = "none";
                    document.getElementById("blogPostStyleSheet").style.display = "none";
                <?php } ?>

                function showStyleInput(that) {
                    if (that.value == "custom") {
                        document.getElementById("blogStyleSheet").style.display = "block";
                        document.getElementById("blogPostStyleSheet").style.display = "block";
                    } else {
                        document.getElementById("blogStyleSheet").style.display = "none";
                        document.getElementById("blogPostStyleSheet").style.display = "none";
                    }
                }
            </script>
        </body>

        </html>
        <?php } else if (count($URI_parts) >= 1 and $URI_parts[0] and $URI_parts[0] == 'post') {
        if (file_exists("config.php")) { ?>
            <!DOCTYPE html>
            <html>

            <head>
                <title><?php echo ($blogName); ?> | New Post</title>
                <link type="text/css" rel="stylesheet" href="https://meyerweb.com/eric/tools/css/reset/reset.css" />
                <link type="text/css" rel="stylesheet" href="https://raw.githack.com/johnroper100/dropplets/master/setup.css" />
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
                <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
            </head>

            <body>
                <main>
                    <div class="setupHeader">
                        <a href="https://github.com/johnroper100/dropplets"><span class="headerLogo"></span><span class="droppletsName">Dropplets</span> </a>
                    </div>
                    <h1>Time to write your prose</h1>
                    <form method="post" action="post">
                        <fieldset>
                            <input type="text" name="blogPostTitle" class="blogPostTitle" placeholder="The post title" required />
                            <textarea name="blogPostContent" id="blogPostContent" placeholder="Write your post here, you can use Markdown"></textarea>
                            <input type="password" name="blogPassword" placeholder="Management password" required />
                        </fieldset>
                        <input type="hidden" name="form" value="post" required />
                        <input class="btn" type="submit" value="Publish New Post" />
                    </form>
                </main>
                <script>
                    var simplemde = new SimpleMDE({
                        element: document.getElementById("blogPostContent")
                    });
                </script>
            </body>

            </html>
        <?php }
    } else if (count($URI_parts) >= 1 and $URI_parts[0] and $URI_parts[0] == 'postUpload') {
        if (file_exists("config.php")) { ?>
            <!DOCTYPE html>
            <html>

            <head>
                <title><?php echo ($blogName); ?> | New Post Upload</title>
                <link type="text/css" rel="stylesheet" href="https://meyerweb.com/eric/tools/css/reset/reset.css" />
                <link type="text/css" rel="stylesheet" href="https://raw.githack.com/johnroper100/dropplets/master/setup.css" />
            </head>

            <body>
                <main>
                    <div class="setupHeader">
                        <a href="https://github.com/johnroper100/dropplets"><span class="headerLogo"></span><span class="droppletsName">Dropplets</span> </a>
                    </div>
                    <h1>Time to write your prose</h1>
                    <form method="post" action="post">
                        <fieldset>
                            <input type="text" name="blogPostTitle" class="blogPostTitle" placeholder="The post title" required />
                            <input type="file" name="blogPostFile" class="blogPostFile" placeholder="The post file" required />
                            <input type="password" name="blogPassword" placeholder="Management password" required />
                        </fieldset>
                        <input type="hidden" name="form" value="postUpload" required />
                        <input class="btn" type="submit" value="Publish New Post" />
                    </form>
                </main>
            </body>

            </html>
        <?php } else {
            header("Location: setup");
        }
    } else if ($URI_parts[0] and $URI_parts[0] == 'update') { ?>
        <!DOCTYPE html>
        <html>

        <head>
            <title><?php echo ($blogName); ?> | Update</title>
            <link type="text/css" rel="stylesheet" href="https://meyerweb.com/eric/tools/css/reset/reset.css" />
            <link type="text/css" rel="stylesheet" href="https://raw.githack.com/johnroper100/dropplets/master/setup.css" />
        </head>

        <body>
            <main>
                <div class="setupHeader">
                    <a href="https://github.com/johnroper100/dropplets"><span class="headerLogo"></span><span class="droppletsName">Dropplets</span> </a>
                </div>
                <h1>Update</h1>
                <h2>The current Dropplets version is: <b>v2.1</b></h2>
                <form method="post" action="post">
                    <fieldset>
                        <legend>Type your password to update your blog:</legend>
                        <input type="password" name="blogPassword" placeholder="Management password" required />
                    </fieldset>
                    <input type="hidden" name="form" value="update" required />
                    <input class="btn" type="submit" value="Update Dropplets" />
                </form>
            </main>
        </body>

        </html>
        <?php } else if (count($URI_parts) >= 3 and $URI_parts[1] == 'posts' and $URI_parts[0]) {
        // If the config exists, read it and display the blog.
        if (file_exists("config.php")) {
            if (file_exists("posts/$URI_parts[0].php")) {
                include "posts/$URI_parts[0].php";
        ?>
                <!DOCTYPE html>
                <html>

                <head>
                    <title><?php echo ($blogName); ?> | <?php echo ($postTitle); ?></title>
                    <link type="text/css" rel="stylesheet" href="https://meyerweb.com/eric/tools/css/reset/reset.css" />
                    <link type="text/css" rel="stylesheet" href="<?php echo ($blogPostStyleSheet); ?>" />
                    <?php echo ($headerInject); ?>
                </head>

                <body>
                    <main id="postPage">
                        <header>
                            <a id="siteTitleLink" href="<?php echo ($SITE_HOME); ?>">➜</a>
                        </header>
                        <div id="postTitleDate">
                            <h1 id="postTitle"><?php echo ($postTitle); ?></h1>
                            <span id="postSubtitle"><?php echo (date("F j, Y, g:i a", $postDate)); ?></span>
                        </div>
                        <div id="postContent"><?php echo ($Extra->text($postContent)); ?></div>
                        <?php if (!empty($blogFooter)) { ?>
                            <div id="footer">
                                <p class="footerText"><?php echo ($blogFooter); ?></p>
                            </div>
                        <?php } ?>
                        <?php echo ($footerInject); ?>
                    </main>
                </body>

                </html>
            <?php
            } else {
            ?>
                The post you were looking for can't be found.
            <?php
            }
        } else {
            header("Location: setup");
        }
    } else {
        // If the config exists, read it and display the blog.
        if (file_exists("config.php")) { ?>
            <!DOCTYPE html>
            <html>

            <head>
                <title><?php echo ($blogName); ?> | Home</title>
                <link type="text/css" rel="stylesheet" href="https://meyerweb.com/eric/tools/css/reset/reset.css" />
                <link type="text/css" rel="stylesheet" href="<?php echo ($blogStyleSheet); ?>" />
                <?php echo ($headerInject); ?>
            </head>

            <body>
                <main id="index">
                    <header>
                        <h1 id="siteTitle"><?php echo ($blogName); ?></h1>
                    </header>
                    <div class="posts">
                        <?php
                        if ($dh = opendir('posts')) {
                            $posts = array();
                            while (($file = readdir($dh)) !== false) {
                                if ($file != '.' and $file != '..' and $file != '...') {
                                    include 'posts/' . $file;
                                    $posts[$postDate] = array('title' => $postTitle, 'date' => $postDate, 'content' => $postContent);
                                }
                            }
                            usort($posts, function ($item1, $item2) {
                                return $item2['date'] <=> $item1['date'];
                            });
                            foreach ($posts as $post) {
                                echo ("<div class=\"post\"><h2 id=\"postTitle\"><a href=\"posts/" . str_replace('.php', '', create_slug($post["title"])) . "\">$post[title]</a></h2><span id=\"postSubtitle\">" . date('F j, Y, g:i a', $post["date"]) . "</span><div id=\"postContent\">" . $Extra->text(substr($post["content"], 0, 250) . "...") . "</div></div>");
                            }
                            closedir($dh);
                        }
                        ?>
                    </div>
                    <?php if (!empty($blogFooter)) { ?>
                        <div id="footer">
                            <p class="footerText"><?php echo ($blogFooter); ?></p>
                        </div>
                    <?php } ?>
                    <?php echo ($footerInject); ?>
                </main>
            </body>

            </html>
<?php } else {
            header("Location: setup");
        }
    }
}
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlentities($data, ENT_QUOTES);
    return $data;
}
function create_slug($string)
{
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $string));
    return $slug;
}
class Parsedown
{
    # ~

    const version = '1.8.0-beta-7';

    # ~

    function text($text)
    {
        $Elements = $this->textElements($text);

        # convert to markup
        $markup = $this->elements($Elements);

        # trim line breaks
        $markup = trim($markup, "\n");

        return $markup;
    }

    protected function textElements($text)
    {
        # make sure no definitions are set
        $this->DefinitionData = array();

        # standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        # remove surrounding line breaks
        $text = trim($text, "\n");

        # split text into lines
        $lines = explode("\n", $text);

        # iterate through lines to identify blocks
        return $this->linesElements($lines);
    }

    #
    # Setters
    #

    function setBreaksEnabled($breaksEnabled)
    {
        $this->breaksEnabled = $breaksEnabled;

        return $this;
    }

    protected $breaksEnabled;

    function setMarkupEscaped($markupEscaped)
    {
        $this->markupEscaped = $markupEscaped;

        return $this;
    }

    protected $markupEscaped;

    function setUrlsLinked($urlsLinked)
    {
        $this->urlsLinked = $urlsLinked;

        return $this;
    }

    protected $urlsLinked = true;

    function setSafeMode($safeMode)
    {
        $this->safeMode = (bool) $safeMode;

        return $this;
    }

    protected $safeMode;

    function setStrictMode($strictMode)
    {
        $this->strictMode = (bool) $strictMode;

        return $this;
    }

    protected $strictMode;

    protected $safeLinksWhitelist = array(
        'http://',
        'https://',
        'ftp://',
        'ftps://',
        'mailto:',
        'tel:',
        'data:image/png;base64,',
        'data:image/gif;base64,',
        'data:image/jpeg;base64,',
        'irc:',
        'ircs:',
        'git:',
        'ssh:',
        'news:',
        'steam:',
    );

    #
    # Lines
    #

    protected $BlockTypes = array(
        '#' => array('Header'),
        '*' => array('Rule', 'List'),
        '+' => array('List'),
        '-' => array('SetextHeader', 'Table', 'Rule', 'List'),
        '0' => array('List'),
        '1' => array('List'),
        '2' => array('List'),
        '3' => array('List'),
        '4' => array('List'),
        '5' => array('List'),
        '6' => array('List'),
        '7' => array('List'),
        '8' => array('List'),
        '9' => array('List'),
        ':' => array('Table'),
        '<' => array('Comment', 'Markup'),
        '=' => array('SetextHeader'),
        '>' => array('Quote'),
        '[' => array('Reference'),
        '_' => array('Rule'),
        '`' => array('FencedCode'),
        '|' => array('Table'),
        '~' => array('FencedCode'),
    );

    # ~

    protected $unmarkedBlockTypes = array(
        'Code',
    );

    #
    # Blocks
    #

    protected function lines(array $lines)
    {
        return $this->elements($this->linesElements($lines));
    }

    protected function linesElements(array $lines)
    {
        $Elements = array();
        $CurrentBlock = null;

        foreach ($lines as $line) {
            if (chop($line) === '') {
                if (isset($CurrentBlock)) {
                    $CurrentBlock['interrupted'] = (isset($CurrentBlock['interrupted'])
                        ? $CurrentBlock['interrupted'] + 1 : 1);
                }

                continue;
            }

            while (($beforeTab = strstr($line, "\t", true)) !== false) {
                $shortage = 4 - mb_strlen($beforeTab, 'utf-8') % 4;

                $line = $beforeTab
                    . str_repeat(' ', $shortage)
                    . substr($line, strlen($beforeTab) + 1);
            }

            $indent = strspn($line, ' ');

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($CurrentBlock['continuable'])) {
                $methodName = 'block' . $CurrentBlock['type'] . 'Continue';
                $Block = $this->$methodName($Line, $CurrentBlock);

                if (isset($Block)) {
                    $CurrentBlock = $Block;

                    continue;
                } else {
                    if ($this->isBlockCompletable($CurrentBlock['type'])) {
                        $methodName = 'block' . $CurrentBlock['type'] . 'Complete';
                        $CurrentBlock = $this->$methodName($CurrentBlock);
                    }
                }
            }

            # ~

            $marker = $text[0];

            # ~

            $blockTypes = $this->unmarkedBlockTypes;

            if (isset($this->BlockTypes[$marker])) {
                foreach ($this->BlockTypes[$marker] as $blockType) {
                    $blockTypes[] = $blockType;
                }
            }

            #
            # ~

            foreach ($blockTypes as $blockType) {
                $Block = $this->{"block$blockType"}($Line, $CurrentBlock);

                if (isset($Block)) {
                    $Block['type'] = $blockType;

                    if (!isset($Block['identified'])) {
                        if (isset($CurrentBlock)) {
                            $Elements[] = $this->extractElement($CurrentBlock);
                        }

                        $Block['identified'] = true;
                    }

                    if ($this->isBlockContinuable($blockType)) {
                        $Block['continuable'] = true;
                    }

                    $CurrentBlock = $Block;

                    continue 2;
                }
            }

            # ~

            if (isset($CurrentBlock) and $CurrentBlock['type'] === 'Paragraph') {
                $Block = $this->paragraphContinue($Line, $CurrentBlock);
            }

            if (isset($Block)) {
                $CurrentBlock = $Block;
            } else {
                if (isset($CurrentBlock)) {
                    $Elements[] = $this->extractElement($CurrentBlock);
                }

                $CurrentBlock = $this->paragraph($Line);

                $CurrentBlock['identified'] = true;
            }
        }

        # ~

        if (isset($CurrentBlock['continuable']) and $this->isBlockCompletable($CurrentBlock['type'])) {
            $methodName = 'block' . $CurrentBlock['type'] . 'Complete';
            $CurrentBlock = $this->$methodName($CurrentBlock);
        }

        # ~

        if (isset($CurrentBlock)) {
            $Elements[] = $this->extractElement($CurrentBlock);
        }

        # ~

        return $Elements;
    }

    protected function extractElement(array $Component)
    {
        if (!isset($Component['element'])) {
            if (isset($Component['markup'])) {
                $Component['element'] = array('rawHtml' => $Component['markup']);
            } elseif (isset($Component['hidden'])) {
                $Component['element'] = array();
            }
        }

        return $Component['element'];
    }

    protected function isBlockContinuable($Type)
    {
        return method_exists($this, 'block' . $Type . 'Continue');
    }

    protected function isBlockCompletable($Type)
    {
        return method_exists($this, 'block' . $Type . 'Complete');
    }

    #
    # Code

    protected function blockCode($Line, $Block = null)
    {
        if (isset($Block) and $Block['type'] === 'Paragraph' and !isset($Block['interrupted'])) {
            return;
        }

        if ($Line['indent'] >= 4) {
            $text = substr($Line['body'], 4);

            $Block = array(
                'element' => array(
                    'name' => 'pre',
                    'element' => array(
                        'name' => 'code',
                        'text' => $text,
                    ),
                ),
            );

            return $Block;
        }
    }

    protected function blockCodeContinue($Line, $Block)
    {
        if ($Line['indent'] >= 4) {
            if (isset($Block['interrupted'])) {
                $Block['element']['element']['text'] .= str_repeat("\n", $Block['interrupted']);

                unset($Block['interrupted']);
            }

            $Block['element']['element']['text'] .= "\n";

            $text = substr($Line['body'], 4);

            $Block['element']['element']['text'] .= $text;

            return $Block;
        }
    }

    protected function blockCodeComplete($Block)
    {
        return $Block;
    }

    #
    # Comment

    protected function blockComment($Line)
    {
        if ($this->markupEscaped or $this->safeMode) {
            return;
        }

        if (strpos($Line['text'], '<!--') === 0) {
            $Block = array(
                'element' => array(
                    'rawHtml' => $Line['body'],
                    'autobreak' => true,
                ),
            );

            if (strpos($Line['text'], '-->') !== false) {
                $Block['closed'] = true;
            }

            return $Block;
        }
    }

    protected function blockCommentContinue($Line, array $Block)
    {
        if (isset($Block['closed'])) {
            return;
        }

        $Block['element']['rawHtml'] .= "\n" . $Line['body'];

        if (strpos($Line['text'], '-->') !== false) {
            $Block['closed'] = true;
        }

        return $Block;
    }

    #
    # Fenced Code

    protected function blockFencedCode($Line)
    {
        $marker = $Line['text'][0];

        $openerLength = strspn($Line['text'], $marker);

        if ($openerLength < 3) {
            return;
        }

        $infostring = trim(substr($Line['text'], $openerLength), "\t ");

        if (strpos($infostring, '`') !== false) {
            return;
        }

        $Element = array(
            'name' => 'code',
            'text' => '',
        );

        if ($infostring !== '') {
            /**
             * https://www.w3.org/TR/2011/WD-html5-20110525/elements.html#classes
             * Every HTML element may have a class attribute specified.
             * The attribute, if specified, must have a value that is a set
             * of space-separated tokens representing the various classes
             * that the element belongs to.
             * [...]
             * The space characters, for the purposes of this specification,
             * are U+0020 SPACE, U+0009 CHARACTER TABULATION (tab),
             * U+000A LINE FEED (LF), U+000C FORM FEED (FF), and
             * U+000D CARRIAGE RETURN (CR).
             */
            $language = substr($infostring, 0, strcspn($infostring, " \t\n\f\r"));

            $Element['attributes'] = array('class' => "language-$language");
        }

        $Block = array(
            'char' => $marker,
            'openerLength' => $openerLength,
            'element' => array(
                'name' => 'pre',
                'element' => $Element,
            ),
        );

        return $Block;
    }

    protected function blockFencedCodeContinue($Line, $Block)
    {
        if (isset($Block['complete'])) {
            return;
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['element']['text'] .= str_repeat("\n", $Block['interrupted']);

            unset($Block['interrupted']);
        }

        if (($len = strspn($Line['text'], $Block['char'])) >= $Block['openerLength']
            and chop(substr($Line['text'], $len), ' ') === ''
        ) {
            $Block['element']['element']['text'] = substr($Block['element']['element']['text'], 1);

            $Block['complete'] = true;

            return $Block;
        }

        $Block['element']['element']['text'] .= "\n" . $Line['body'];

        return $Block;
    }

    protected function blockFencedCodeComplete($Block)
    {
        return $Block;
    }

    #
    # Header

    protected function blockHeader($Line)
    {
        $level = strspn($Line['text'], '#');

        if ($level > 6) {
            return;
        }

        $text = trim($Line['text'], '#');

        if ($this->strictMode and isset($text[0]) and $text[0] !== ' ') {
            return;
        }

        $text = trim($text, ' ');

        $Block = array(
            'element' => array(
                'name' => 'h' . $level,
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $text,
                    'destination' => 'elements',
                )
            ),
        );

        return $Block;
    }

    #
    # List

    protected function blockList($Line, array $CurrentBlock = null)
    {
        list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]{1,9}+[.\)]');

        if (preg_match('/^(' . $pattern . '([ ]++|$))(.*+)/', $Line['text'], $matches)) {
            $contentIndent = strlen($matches[2]);

            if ($contentIndent >= 5) {
                $contentIndent -= 1;
                $matches[1] = substr($matches[1], 0, -$contentIndent);
                $matches[3] = str_repeat(' ', $contentIndent) . $matches[3];
            } elseif ($contentIndent === 0) {
                $matches[1] .= ' ';
            }

            $markerWithoutWhitespace = strstr($matches[1], ' ', true);

            $Block = array(
                'indent' => $Line['indent'],
                'pattern' => $pattern,
                'data' => array(
                    'type' => $name,
                    'marker' => $matches[1],
                    'markerType' => ($name === 'ul' ? $markerWithoutWhitespace : substr($markerWithoutWhitespace, -1)),
                ),
                'element' => array(
                    'name' => $name,
                    'elements' => array(),
                ),
            );
            $Block['data']['markerTypeRegex'] = preg_quote($Block['data']['markerType'], '/');

            if ($name === 'ol') {
                $listStart = ltrim(strstr($matches[1], $Block['data']['markerType'], true), '0') ?: '0';

                if ($listStart !== '1') {
                    if (
                        isset($CurrentBlock)
                        and $CurrentBlock['type'] === 'Paragraph'
                        and !isset($CurrentBlock['interrupted'])
                    ) {
                        return;
                    }

                    $Block['element']['attributes'] = array('start' => $listStart);
                }
            }

            $Block['li'] = array(
                'name' => 'li',
                'handler' => array(
                    'function' => 'li',
                    'argument' => !empty($matches[3]) ? array($matches[3]) : array(),
                    'destination' => 'elements'
                )
            );

            $Block['element']['elements'][] = &$Block['li'];

            return $Block;
        }
    }

    protected function blockListContinue($Line, array $Block)
    {
        if (isset($Block['interrupted']) and empty($Block['li']['handler']['argument'])) {
            return null;
        }

        $requiredIndent = ($Block['indent'] + strlen($Block['data']['marker']));

        if (
            $Line['indent'] < $requiredIndent
            and (
                ($Block['data']['type'] === 'ol'
                    and preg_match('/^[0-9]++' . $Block['data']['markerTypeRegex'] . '(?:[ ]++(.*)|$)/', $Line['text'], $matches)) or ($Block['data']['type'] === 'ul'
                    and preg_match('/^' . $Block['data']['markerTypeRegex'] . '(?:[ ]++(.*)|$)/', $Line['text'], $matches)))
        ) {
            if (isset($Block['interrupted'])) {
                $Block['li']['handler']['argument'][] = '';

                $Block['loose'] = true;

                unset($Block['interrupted']);
            }

            unset($Block['li']);

            $text = isset($matches[1]) ? $matches[1] : '';

            $Block['indent'] = $Line['indent'];

            $Block['li'] = array(
                'name' => 'li',
                'handler' => array(
                    'function' => 'li',
                    'argument' => array($text),
                    'destination' => 'elements'
                )
            );

            $Block['element']['elements'][] = &$Block['li'];

            return $Block;
        } elseif ($Line['indent'] < $requiredIndent and $this->blockList($Line)) {
            return null;
        }

        if ($Line['text'][0] === '[' and $this->blockReference($Line)) {
            return $Block;
        }

        if ($Line['indent'] >= $requiredIndent) {
            if (isset($Block['interrupted'])) {
                $Block['li']['handler']['argument'][] = '';

                $Block['loose'] = true;

                unset($Block['interrupted']);
            }

            $text = substr($Line['body'], $requiredIndent);

            $Block['li']['handler']['argument'][] = $text;

            return $Block;
        }

        if (!isset($Block['interrupted'])) {
            $text = preg_replace('/^[ ]{0,' . $requiredIndent . '}+/', '', $Line['body']);

            $Block['li']['handler']['argument'][] = $text;

            return $Block;
        }
    }

    protected function blockListComplete(array $Block)
    {
        if (isset($Block['loose'])) {
            foreach ($Block['element']['elements'] as &$li) {
                if (end($li['handler']['argument']) !== '') {
                    $li['handler']['argument'][] = '';
                }
            }
        }

        return $Block;
    }

    #
    # Quote

    protected function blockQuote($Line)
    {
        if (preg_match('/^>[ ]?+(.*+)/', $Line['text'], $matches)) {
            $Block = array(
                'element' => array(
                    'name' => 'blockquote',
                    'handler' => array(
                        'function' => 'linesElements',
                        'argument' => (array) $matches[1],
                        'destination' => 'elements',
                    )
                ),
            );

            return $Block;
        }
    }

    protected function blockQuoteContinue($Line, array $Block)
    {
        if (isset($Block['interrupted'])) {
            return;
        }

        if ($Line['text'][0] === '>' and preg_match('/^>[ ]?+(.*+)/', $Line['text'], $matches)) {
            $Block['element']['handler']['argument'][] = $matches[1];

            return $Block;
        }

        if (!isset($Block['interrupted'])) {
            $Block['element']['handler']['argument'][] = $Line['text'];

            return $Block;
        }
    }

    #
    # Rule

    protected function blockRule($Line)
    {
        $marker = $Line['text'][0];

        if (substr_count($Line['text'], $marker) >= 3 and chop($Line['text'], " $marker") === '') {
            $Block = array(
                'element' => array(
                    'name' => 'hr',
                ),
            );

            return $Block;
        }
    }

    #
    # Setext

    protected function blockSetextHeader($Line, array $Block = null)
    {
        if (!isset($Block) or $Block['type'] !== 'Paragraph' or isset($Block['interrupted'])) {
            return;
        }

        if ($Line['indent'] < 4 and chop(chop($Line['text'], ' '), $Line['text'][0]) === '') {
            $Block['element']['name'] = $Line['text'][0] === '=' ? 'h1' : 'h2';

            return $Block;
        }
    }

    #
    # Markup

    protected function blockMarkup($Line)
    {
        if ($this->markupEscaped or $this->safeMode) {
            return;
        }

        if (preg_match('/^<[\/]?+(\w*)(?:[ ]*+' . $this->regexHtmlAttribute . ')*+[ ]*+(\/)?>/', $Line['text'], $matches)) {
            $element = strtolower($matches[1]);

            if (in_array($element, $this->textLevelElements)) {
                return;
            }

            $Block = array(
                'name' => $matches[1],
                'element' => array(
                    'rawHtml' => $Line['text'],
                    'autobreak' => true,
                ),
            );

            return $Block;
        }
    }

    protected function blockMarkupContinue($Line, array $Block)
    {
        if (isset($Block['closed']) or isset($Block['interrupted'])) {
            return;
        }

        $Block['element']['rawHtml'] .= "\n" . $Line['body'];

        return $Block;
    }

    #
    # Reference

    protected function blockReference($Line)
    {
        if (
            strpos($Line['text'], ']') !== false
            and preg_match('/^\[(.+?)\]:[ ]*+<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*+$/', $Line['text'], $matches)
        ) {
            $id = strtolower($matches[1]);

            $Data = array(
                'url' => $matches[2],
                'title' => isset($matches[3]) ? $matches[3] : null,
            );

            $this->DefinitionData['Reference'][$id] = $Data;

            $Block = array(
                'element' => array(),
            );

            return $Block;
        }
    }

    #
    # Table

    protected function blockTable($Line, array $Block = null)
    {
        if (!isset($Block) or $Block['type'] !== 'Paragraph' or isset($Block['interrupted'])) {
            return;
        }

        if (
            strpos($Block['element']['handler']['argument'], '|') === false
            and strpos($Line['text'], '|') === false
            and strpos($Line['text'], ':') === false
            or strpos($Block['element']['handler']['argument'], "\n") !== false
        ) {
            return;
        }

        if (chop($Line['text'], ' -:|') !== '') {
            return;
        }

        $alignments = array();

        $divider = $Line['text'];

        $divider = trim($divider);
        $divider = trim($divider, '|');

        $dividerCells = explode('|', $divider);

        foreach ($dividerCells as $dividerCell) {
            $dividerCell = trim($dividerCell);

            if ($dividerCell === '') {
                return;
            }

            $alignment = null;

            if ($dividerCell[0] === ':') {
                $alignment = 'left';
            }

            if (substr($dividerCell, -1) === ':') {
                $alignment = $alignment === 'left' ? 'center' : 'right';
            }

            $alignments[] = $alignment;
        }

        # ~

        $HeaderElements = array();

        $header = $Block['element']['handler']['argument'];

        $header = trim($header);
        $header = trim($header, '|');

        $headerCells = explode('|', $header);

        if (count($headerCells) !== count($alignments)) {
            return;
        }

        foreach ($headerCells as $index => $headerCell) {
            $headerCell = trim($headerCell);

            $HeaderElement = array(
                'name' => 'th',
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $headerCell,
                    'destination' => 'elements',
                )
            );

            if (isset($alignments[$index])) {
                $alignment = $alignments[$index];

                $HeaderElement['attributes'] = array(
                    'style' => "text-align: $alignment;",
                );
            }

            $HeaderElements[] = $HeaderElement;
        }

        # ~

        $Block = array(
            'alignments' => $alignments,
            'identified' => true,
            'element' => array(
                'name' => 'table',
                'elements' => array(),
            ),
        );

        $Block['element']['elements'][] = array(
            'name' => 'thead',
        );

        $Block['element']['elements'][] = array(
            'name' => 'tbody',
            'elements' => array(),
        );

        $Block['element']['elements'][0]['elements'][] = array(
            'name' => 'tr',
            'elements' => $HeaderElements,
        );

        return $Block;
    }

    protected function blockTableContinue($Line, array $Block)
    {
        if (isset($Block['interrupted'])) {
            return;
        }

        if (count($Block['alignments']) === 1 or $Line['text'][0] === '|' or strpos($Line['text'], '|')) {
            $Elements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]++`|`)++/', $row, $matches);

            $cells = array_slice($matches[0], 0, count($Block['alignments']));

            foreach ($cells as $index => $cell) {
                $cell = trim($cell);

                $Element = array(
                    'name' => 'td',
                    'handler' => array(
                        'function' => 'lineElements',
                        'argument' => $cell,
                        'destination' => 'elements',
                    )
                );

                if (isset($Block['alignments'][$index])) {
                    $Element['attributes'] = array(
                        'style' => 'text-align: ' . $Block['alignments'][$index] . ';',
                    );
                }

                $Elements[] = $Element;
            }

            $Element = array(
                'name' => 'tr',
                'elements' => $Elements,
            );

            $Block['element']['elements'][1]['elements'][] = $Element;

            return $Block;
        }
    }

    #
    # ~
    #

    protected function paragraph($Line)
    {
        return array(
            'type' => 'Paragraph',
            'element' => array(
                'name' => 'p',
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $Line['text'],
                    'destination' => 'elements',
                ),
            ),
        );
    }

    protected function paragraphContinue($Line, array $Block)
    {
        if (isset($Block['interrupted'])) {
            return;
        }

        $Block['element']['handler']['argument'] .= "\n" . $Line['text'];

        return $Block;
    }

    #
    # Inline Elements
    #

    protected $InlineTypes = array(
        '!' => array('Image'),
        '&' => array('SpecialCharacter'),
        '*' => array('Emphasis'),
        ':' => array('Url'),
        '<' => array('UrlTag', 'EmailTag', 'Markup'),
        '[' => array('Link'),
        '_' => array('Emphasis'),
        '`' => array('Code'),
        '~' => array('Strikethrough'),
        '\\' => array('EscapeSequence'),
    );

    # ~

    protected $inlineMarkerList = '!*_&[:<`~\\';

    #
    # ~
    #

    public function line($text, $nonNestables = array())
    {
        return $this->elements($this->lineElements($text, $nonNestables));
    }

    protected function lineElements($text, $nonNestables = array())
    {
        # standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        $Elements = array();

        $nonNestables = (empty($nonNestables)
            ? array()
            : array_combine($nonNestables, $nonNestables));

        # $excerpt is based on the first occurrence of a marker

        while ($excerpt = strpbrk($text, $this->inlineMarkerList)) {
            $marker = $excerpt[0];

            $markerPosition = strlen($text) - strlen($excerpt);

            $Excerpt = array('text' => $excerpt, 'context' => $text);

            foreach ($this->InlineTypes[$marker] as $inlineType) {
                # check to see if the current inline type is nestable in the current context

                if (isset($nonNestables[$inlineType])) {
                    continue;
                }

                $Inline = $this->{"inline$inlineType"}($Excerpt);

                if (!isset($Inline)) {
                    continue;
                }

                # makes sure that the inline belongs to "our" marker

                if (isset($Inline['position']) and $Inline['position'] > $markerPosition) {
                    continue;
                }

                # sets a default inline position

                if (!isset($Inline['position'])) {
                    $Inline['position'] = $markerPosition;
                }

                # cause the new element to 'inherit' our non nestables


                $Inline['element']['nonNestables'] = isset($Inline['element']['nonNestables'])
                    ? array_merge($Inline['element']['nonNestables'], $nonNestables)
                    : $nonNestables;

                # the text that comes before the inline
                $unmarkedText = substr($text, 0, $Inline['position']);

                # compile the unmarked text
                $InlineText = $this->inlineText($unmarkedText);
                $Elements[] = $InlineText['element'];

                # compile the inline
                $Elements[] = $this->extractElement($Inline);

                # remove the examined text
                $text = substr($text, $Inline['position'] + $Inline['extent']);

                continue 2;
            }

            # the marker does not belong to an inline

            $unmarkedText = substr($text, 0, $markerPosition + 1);

            $InlineText = $this->inlineText($unmarkedText);
            $Elements[] = $InlineText['element'];

            $text = substr($text, $markerPosition + 1);
        }

        $InlineText = $this->inlineText($text);
        $Elements[] = $InlineText['element'];

        foreach ($Elements as &$Element) {
            if (!isset($Element['autobreak'])) {
                $Element['autobreak'] = false;
            }
        }

        return $Elements;
    }

    #
    # ~
    #

    protected function inlineText($text)
    {
        $Inline = array(
            'extent' => strlen($text),
            'element' => array(),
        );

        $Inline['element']['elements'] = self::pregReplaceElements(
            $this->breaksEnabled ? '/[ ]*+\n/' : '/(?:[ ]*+\\\\|[ ]{2,}+)\n/',
            array(
                array('name' => 'br'),
                array('text' => "\n"),
            ),
            $text
        );

        return $Inline;
    }

    protected function inlineCode($Excerpt)
    {
        $marker = $Excerpt['text'][0];

        if (preg_match('/^([' . $marker . ']++)[ ]*+(.+?)[ ]*+(?<![' . $marker . '])\1(?!' . $marker . ')/s', $Excerpt['text'], $matches)) {
            $text = $matches[2];
            $text = preg_replace('/[ ]*+\n/', ' ', $text);

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'code',
                    'text' => $text,
                ),
            );
        }
    }

    protected function inlineEmailTag($Excerpt)
    {
        $hostnameLabel = '[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?';

        $commonMarkEmail = '[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]++@'
            . $hostnameLabel . '(?:\.' . $hostnameLabel . ')*';

        if (
            strpos($Excerpt['text'], '>') !== false
            and preg_match("/^<((mailto:)?$commonMarkEmail)>/i", $Excerpt['text'], $matches)
        ) {
            $url = $matches[1];

            if (!isset($matches[2])) {
                $url = "mailto:$url";
            }

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[1],
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
    }

    protected function inlineEmphasis($Excerpt)
    {
        if (!isset($Excerpt['text'][1])) {
            return;
        }

        $marker = $Excerpt['text'][0];

        if ($Excerpt['text'][1] === $marker and preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'strong';
        } elseif (preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'em';
        } else {
            return;
        }

        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => $emphasis,
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $matches[1],
                    'destination' => 'elements',
                )
            ),
        );
    }

    protected function inlineEscapeSequence($Excerpt)
    {
        if (isset($Excerpt['text'][1]) and in_array($Excerpt['text'][1], $this->specialCharacters)) {
            return array(
                'element' => array('rawHtml' => $Excerpt['text'][1]),
                'extent' => 2,
            );
        }
    }

    protected function inlineImage($Excerpt)
    {
        if (!isset($Excerpt['text'][1]) or $Excerpt['text'][1] !== '[') {
            return;
        }

        $Excerpt['text'] = substr($Excerpt['text'], 1);

        $Link = $this->inlineLink($Excerpt);

        if ($Link === null) {
            return;
        }

        $Inline = array(
            'extent' => $Link['extent'] + 1,
            'element' => array(
                'name' => 'img',
                'attributes' => array(
                    'src' => $Link['element']['attributes']['href'],
                    'alt' => $Link['element']['handler']['argument'],
                ),
                'autobreak' => true,
            ),
        );

        $Inline['element']['attributes'] += $Link['element']['attributes'];

        unset($Inline['element']['attributes']['href']);

        return $Inline;
    }

    protected function inlineLink($Excerpt)
    {
        $Element = array(
            'name' => 'a',
            'handler' => array(
                'function' => 'lineElements',
                'argument' => null,
                'destination' => 'elements',
            ),
            'nonNestables' => array('Url', 'Link'),
            'attributes' => array(
                'href' => null,
                'title' => null,
            ),
        );

        $extent = 0;

        $remainder = $Excerpt['text'];

        if (preg_match('/\[((?:[^][]++|(?R))*+)\]/', $remainder, $matches)) {
            $Element['handler']['argument'] = $matches[1];

            $extent += strlen($matches[0]);

            $remainder = substr($remainder, $extent);
        } else {
            return;
        }

        if (preg_match('/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)(?:[ ]+("[^"]*+"|\'[^\']*+\'))?\s*+[)]/', $remainder, $matches)) {
            $Element['attributes']['href'] = $matches[1];

            if (isset($matches[2])) {
                $Element['attributes']['title'] = substr($matches[2], 1, -1);
            }

            $extent += strlen($matches[0]);
        } else {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches)) {
                $definition = strlen($matches[1]) ? $matches[1] : $Element['handler']['argument'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
            } else {
                $definition = strtolower($Element['handler']['argument']);
            }

            if (!isset($this->DefinitionData['Reference'][$definition])) {
                return;
            }

            $Definition = $this->DefinitionData['Reference'][$definition];

            $Element['attributes']['href'] = $Definition['url'];
            $Element['attributes']['title'] = $Definition['title'];
        }

        return array(
            'extent' => $extent,
            'element' => $Element,
        );
    }

    protected function inlineMarkup($Excerpt)
    {
        if ($this->markupEscaped or $this->safeMode or strpos($Excerpt['text'], '>') === false) {
            return;
        }

        if ($Excerpt['text'][1] === '/' and preg_match('/^<\/\w[\w-]*+[ ]*+>/s', $Excerpt['text'], $matches)) {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }

        if ($Excerpt['text'][1] === '!' and preg_match('/^<!---?[^>-](?:-?+[^-])*-->/s', $Excerpt['text'], $matches)) {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }

        if ($Excerpt['text'][1] !== ' ' and preg_match('/^<\w[\w-]*+(?:[ ]*+' . $this->regexHtmlAttribute . ')*+[ ]*+\/?>/s', $Excerpt['text'], $matches)) {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }
    }

    protected function inlineSpecialCharacter($Excerpt)
    {
        if (
            substr($Excerpt['text'], 1, 1) !== ' ' and strpos($Excerpt['text'], ';') !== false
            and preg_match('/^&(#?+[0-9a-zA-Z]++);/', $Excerpt['text'], $matches)
        ) {
            return array(
                'element' => array('rawHtml' => '&' . $matches[1] . ';'),
                'extent' => strlen($matches[0]),
            );
        }

        return;
    }

    protected function inlineStrikethrough($Excerpt)
    {
        if (!isset($Excerpt['text'][1])) {
            return;
        }

        if ($Excerpt['text'][1] === '~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $Excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'del',
                    'handler' => array(
                        'function' => 'lineElements',
                        'argument' => $matches[1],
                        'destination' => 'elements',
                    )
                ),
            );
        }
    }

    protected function inlineUrl($Excerpt)
    {
        if ($this->urlsLinked !== true or !isset($Excerpt['text'][2]) or $Excerpt['text'][2] !== '/') {
            return;
        }

        if (
            strpos($Excerpt['context'], 'http') !== false
            and preg_match('/\bhttps?+:[\/]{2}[^\s<]+\b\/*+/ui', $Excerpt['context'], $matches, PREG_OFFSET_CAPTURE)
        ) {
            $url = $matches[0][0];

            $Inline = array(
                'extent' => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );

            return $Inline;
        }
    }

    protected function inlineUrlTag($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<(\w++:\/{2}[^ >]++)>/i', $Excerpt['text'], $matches)) {
            $url = $matches[1];

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
    }

    # ~

    protected function unmarkedText($text)
    {
        $Inline = $this->inlineText($text);
        return $this->element($Inline['element']);
    }

    #
    # Handlers
    #

    protected function handle(array $Element)
    {
        if (isset($Element['handler'])) {
            if (!isset($Element['nonNestables'])) {
                $Element['nonNestables'] = array();
            }

            if (is_string($Element['handler'])) {
                $function = $Element['handler'];
                $argument = $Element['text'];
                unset($Element['text']);
                $destination = 'rawHtml';
            } else {
                $function = $Element['handler']['function'];
                $argument = $Element['handler']['argument'];
                $destination = $Element['handler']['destination'];
            }

            $Element[$destination] = $this->{$function}($argument, $Element['nonNestables']);

            if ($destination === 'handler') {
                $Element = $this->handle($Element);
            }

            unset($Element['handler']);
        }

        return $Element;
    }

    protected function handleElementRecursive(array $Element)
    {
        return $this->elementApplyRecursive(array($this, 'handle'), $Element);
    }

    protected function handleElementsRecursive(array $Elements)
    {
        return $this->elementsApplyRecursive(array($this, 'handle'), $Elements);
    }

    protected function elementApplyRecursive($closure, array $Element)
    {
        $Element = call_user_func($closure, $Element);

        if (isset($Element['elements'])) {
            $Element['elements'] = $this->elementsApplyRecursive($closure, $Element['elements']);
        } elseif (isset($Element['element'])) {
            $Element['element'] = $this->elementApplyRecursive($closure, $Element['element']);
        }

        return $Element;
    }

    protected function elementApplyRecursiveDepthFirst($closure, array $Element)
    {
        if (isset($Element['elements'])) {
            $Element['elements'] = $this->elementsApplyRecursiveDepthFirst($closure, $Element['elements']);
        } elseif (isset($Element['element'])) {
            $Element['element'] = $this->elementsApplyRecursiveDepthFirst($closure, $Element['element']);
        }

        $Element = call_user_func($closure, $Element);

        return $Element;
    }

    protected function elementsApplyRecursive($closure, array $Elements)
    {
        foreach ($Elements as &$Element) {
            $Element = $this->elementApplyRecursive($closure, $Element);
        }

        return $Elements;
    }

    protected function elementsApplyRecursiveDepthFirst($closure, array $Elements)
    {
        foreach ($Elements as &$Element) {
            $Element = $this->elementApplyRecursiveDepthFirst($closure, $Element);
        }

        return $Elements;
    }

    protected function element(array $Element)
    {
        if ($this->safeMode) {
            $Element = $this->sanitiseElement($Element);
        }

        # identity map if element has no handler
        $Element = $this->handle($Element);

        $hasName = isset($Element['name']);

        $markup = '';

        if ($hasName) {
            $markup .= '<' . $Element['name'];

            if (isset($Element['attributes'])) {
                foreach ($Element['attributes'] as $name => $value) {
                    if ($value === null) {
                        continue;
                    }

                    $markup .= " $name=\"" . self::escape($value) . '"';
                }
            }
        }

        $permitRawHtml = false;

        if (isset($Element['text'])) {
            $text = $Element['text'];
        }
        // very strongly consider an alternative if you're writing an
        // extension
        elseif (isset($Element['rawHtml'])) {
            $text = $Element['rawHtml'];

            $allowRawHtmlInSafeMode = isset($Element['allowRawHtmlInSafeMode']) && $Element['allowRawHtmlInSafeMode'];
            $permitRawHtml = !$this->safeMode || $allowRawHtmlInSafeMode;
        }

        $hasContent = isset($text) || isset($Element['element']) || isset($Element['elements']);

        if ($hasContent) {
            $markup .= $hasName ? '>' : '';

            if (isset($Element['elements'])) {
                $markup .= $this->elements($Element['elements']);
            } elseif (isset($Element['element'])) {
                $markup .= $this->element($Element['element']);
            } else {
                if (!$permitRawHtml) {
                    $markup .= self::escape($text, true);
                } else {
                    $markup .= $text;
                }
            }

            $markup .= $hasName ? '</' . $Element['name'] . '>' : '';
        } elseif ($hasName) {
            $markup .= ' />';
        }

        return $markup;
    }

    protected function elements(array $Elements)
    {
        $markup = '';

        $autoBreak = true;

        foreach ($Elements as $Element) {
            if (empty($Element)) {
                continue;
            }

            $autoBreakNext = (isset($Element['autobreak'])
                ? $Element['autobreak'] : isset($Element['name']));
            // (autobreak === false) covers both sides of an element
            $autoBreak = !$autoBreak ? $autoBreak : $autoBreakNext;

            $markup .= ($autoBreak ? "\n" : '') . $this->element($Element);
            $autoBreak = $autoBreakNext;
        }

        $markup .= $autoBreak ? "\n" : '';

        return $markup;
    }

    # ~

    protected function li($lines)
    {
        $Elements = $this->linesElements($lines);

        if (
            !in_array('', $lines)
            and isset($Elements[0]) and isset($Elements[0]['name'])
            and $Elements[0]['name'] === 'p'
        ) {
            unset($Elements[0]['name']);
        }

        return $Elements;
    }

    #
    # AST Convenience
    #

    /**
     * Replace occurrences $regexp with $Elements in $text. Return an array of
     * elements representing the replacement.
     */
    protected static function pregReplaceElements($regexp, $Elements, $text)
    {
        $newElements = array();

        while (preg_match($regexp, $text, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = $matches[0][1];
            $before = substr($text, 0, $offset);
            $after = substr($text, $offset + strlen($matches[0][0]));

            $newElements[] = array('text' => $before);

            foreach ($Elements as $Element) {
                $newElements[] = $Element;
            }

            $text = $after;
        }

        $newElements[] = array('text' => $text);

        return $newElements;
    }

    #
    # Deprecated Methods
    #

    function parse($text)
    {
        $markup = $this->text($text);

        return $markup;
    }

    protected function sanitiseElement(array $Element)
    {
        static $goodAttribute = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*+$/';
        static $safeUrlNameToAtt  = array(
            'a'   => 'href',
            'img' => 'src',
        );

        if (!isset($Element['name'])) {
            unset($Element['attributes']);
            return $Element;
        }

        if (isset($safeUrlNameToAtt[$Element['name']])) {
            $Element = $this->filterUnsafeUrlInAttribute($Element, $safeUrlNameToAtt[$Element['name']]);
        }

        if (!empty($Element['attributes'])) {
            foreach ($Element['attributes'] as $att => $val) {
                # filter out badly parsed attribute
                if (!preg_match($goodAttribute, $att)) {
                    unset($Element['attributes'][$att]);
                }
                # dump onevent attribute
                elseif (self::striAtStart($att, 'on')) {
                    unset($Element['attributes'][$att]);
                }
            }
        }

        return $Element;
    }

    protected function filterUnsafeUrlInAttribute(array $Element, $attribute)
    {
        foreach ($this->safeLinksWhitelist as $scheme) {
            if (self::striAtStart($Element['attributes'][$attribute], $scheme)) {
                return $Element;
            }
        }

        $Element['attributes'][$attribute] = str_replace(':', '%3A', $Element['attributes'][$attribute]);

        return $Element;
    }

    #
    # Static Methods
    #

    protected static function escape($text, $allowQuotes = false)
    {
        return htmlspecialchars($text, $allowQuotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }

    protected static function striAtStart($string, $needle)
    {
        $len = strlen($needle);

        if ($len > strlen($string)) {
            return false;
        } else {
            return strtolower(substr($string, 0, $len)) === strtolower($needle);
        }
    }

    static function instance($name = 'default')
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $instance = new static();

        self::$instances[$name] = $instance;

        return $instance;
    }

    private static $instances = array();

    #
    # Fields
    #

    protected $DefinitionData;

    #
    # Read-Only

    protected $specialCharacters = array(
        '\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '>', '#', '+', '-', '.', '!', '|', '~'
    );

    protected $StrongRegex = array(
        '*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*+[*])+?)[*]{2}(?![*])/s',
        '_' => '/^__((?:\\\\_|[^_]|_[^_]*+_)+?)__(?!_)/us',
    );

    protected $EmRegex = array(
        '*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
        '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
    );

    protected $regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*+(?:\s*+=\s*+(?:[^"\'=<>`\s]+|"[^"]*+"|\'[^\']*+\'))?+';

    protected $voidElements = array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source',
    );

    protected $textLevelElements = array(
        'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
        'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing',
        'i', 'rp', 'del', 'code',          'strike', 'marquee',
        'q', 'rt', 'ins', 'font',          'strong',
        's', 'tt', 'kbd', 'mark',
        'u', 'xm', 'sub', 'nobr',
        'sup', 'ruby',
        'var', 'span',
        'wbr', 'time',
    );
}
class ParsedownExtra extends Parsedown
{
    # ~

    const version = '0.8.0';

    # ~

    function __construct()
    {
        if (version_compare(parent::version, '1.7.1') < 0) {
            throw new Exception('ParsedownExtra requires a later version of Parsedown');
        }

        $this->BlockTypes[':'][] = 'DefinitionList';
        $this->BlockTypes['*'][] = 'Abbreviation';

        # identify footnote definitions before reference definitions
        array_unshift($this->BlockTypes['['], 'Footnote');

        # identify footnote markers before before links
        array_unshift($this->InlineTypes['['], 'FootnoteMarker');
    }

    #
    # ~

    function text($text)
    {
        $Elements = $this->textElements($text);

        # convert to markup
        $markup = $this->elements($Elements);

        # trim line breaks
        $markup = trim($markup, "\n");

        # merge consecutive dl elements

        $markup = preg_replace('/<\/dl>\s+<dl>\s+/', '', $markup);

        # add footnotes

        if (isset($this->DefinitionData['Footnote'])) {
            $Element = $this->buildFootnoteElement();

            $markup .= "\n" . $this->element($Element);
        }

        return $markup;
    }

    #
    # Blocks
    #

    #
    # Abbreviation

    protected function blockAbbreviation($Line)
    {
        if (preg_match('/^\*\[(.+?)\]:[ ]*(.+?)[ ]*$/', $Line['text'], $matches)) {
            $this->DefinitionData['Abbreviation'][$matches[1]] = $matches[2];

            $Block = array(
                'hidden' => true,
            );

            return $Block;
        }
    }

    #
    # Footnote

    protected function blockFootnote($Line)
    {
        if (preg_match('/^\[\^(.+?)\]:[ ]?(.*)$/', $Line['text'], $matches)) {
            $Block = array(
                'label' => $matches[1],
                'text' => $matches[2],
                'hidden' => true,
            );

            return $Block;
        }
    }

    protected function blockFootnoteContinue($Line, $Block)
    {
        if ($Line['text'][0] === '[' and preg_match('/^\[\^(.+?)\]:/', $Line['text'])) {
            return;
        }

        if (isset($Block['interrupted'])) {
            if ($Line['indent'] >= 4) {
                $Block['text'] .= "\n\n" . $Line['text'];

                return $Block;
            }
        } else {
            $Block['text'] .= "\n" . $Line['text'];

            return $Block;
        }
    }

    protected function blockFootnoteComplete($Block)
    {
        $this->DefinitionData['Footnote'][$Block['label']] = array(
            'text' => $Block['text'],
            'count' => null,
            'number' => null,
        );

        return $Block;
    }

    #
    # Definition List

    protected function blockDefinitionList($Line, $Block)
    {
        if (!isset($Block) or $Block['type'] !== 'Paragraph') {
            return;
        }

        $Element = array(
            'name' => 'dl',
            'elements' => array(),
        );

        $terms = explode("\n", $Block['element']['handler']['argument']);

        foreach ($terms as $term) {
            $Element['elements'][] = array(
                'name' => 'dt',
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $term,
                    'destination' => 'elements'
                ),
            );
        }

        $Block['element'] = $Element;

        $Block = $this->addDdElement($Line, $Block);

        return $Block;
    }

    protected function blockDefinitionListContinue($Line, array $Block)
    {
        if ($Line['text'][0] === ':') {
            $Block = $this->addDdElement($Line, $Block);

            return $Block;
        } else {
            if (isset($Block['interrupted']) and $Line['indent'] === 0) {
                return;
            }

            if (isset($Block['interrupted'])) {
                $Block['dd']['handler']['function'] = 'textElements';
                $Block['dd']['handler']['argument'] .= "\n\n";

                $Block['dd']['handler']['destination'] = 'elements';

                unset($Block['interrupted']);
            }

            $text = substr($Line['body'], min($Line['indent'], 4));

            $Block['dd']['handler']['argument'] .= "\n" . $text;

            return $Block;
        }
    }

    #
    # Header

    protected function blockHeader($Line)
    {
        $Block = parent::blockHeader($Line);

        if ($Block !== null && preg_match('/[ #]*{(' . $this->regexAttribute . '+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];

            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);

            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        return $Block;
    }

    #
    # Markup

    protected function blockMarkup($Line)
    {
        if ($this->markupEscaped or $this->safeMode) {
            return;
        }

        if (preg_match('/^<(\w[\w-]*)(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*(\/)?>/', $Line['text'], $matches)) {
            $element = strtolower($matches[1]);

            if (in_array($element, $this->textLevelElements)) {
                return;
            }

            $Block = array(
                'name' => $matches[1],
                'depth' => 0,
                'element' => array(
                    'rawHtml' => $Line['text'],
                    'autobreak' => true,
                ),
            );

            $length = strlen($matches[0]);
            $remainder = substr($Line['text'], $length);

            if (trim($remainder) === '') {
                if (isset($matches[2]) or in_array($matches[1], $this->voidElements)) {
                    $Block['closed'] = true;
                    $Block['void'] = true;
                }
            } else {
                if (isset($matches[2]) or in_array($matches[1], $this->voidElements)) {
                    return;
                }
                if (preg_match('/<\/' . $matches[1] . '>[ ]*$/i', $remainder)) {
                    $Block['closed'] = true;
                }
            }

            return $Block;
        }
    }

    protected function blockMarkupContinue($Line, array $Block)
    {
        if (isset($Block['closed'])) {
            return;
        }

        if (preg_match('/^<' . $Block['name'] . '(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*>/i', $Line['text'])) # open
        {
            $Block['depth']++;
        }

        if (preg_match('/(.*?)<\/' . $Block['name'] . '>[ ]*$/i', $Line['text'], $matches)) # close
        {
            if ($Block['depth'] > 0) {
                $Block['depth']--;
            } else {
                $Block['closed'] = true;
            }
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['rawHtml'] .= "\n";
            unset($Block['interrupted']);
        }

        $Block['element']['rawHtml'] .= "\n" . $Line['body'];

        return $Block;
    }

    protected function blockMarkupComplete($Block)
    {
        if (!isset($Block['void'])) {
            $Block['element']['rawHtml'] = $this->processTag($Block['element']['rawHtml']);
        }

        return $Block;
    }

    #
    # Setext

    protected function blockSetextHeader($Line, array $Block = null)
    {
        $Block = parent::blockSetextHeader($Line, $Block);

        if ($Block !== null && preg_match('/[ ]*{(' . $this->regexAttribute . '+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];

            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);

            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        return $Block;
    }

    #
    # Inline Elements
    #

    #
    # Footnote Marker

    protected function inlineFootnoteMarker($Excerpt)
    {
        if (preg_match('/^\[\^(.+?)\]/', $Excerpt['text'], $matches)) {
            $name = $matches[1];

            if (!isset($this->DefinitionData['Footnote'][$name])) {
                return;
            }

            $this->DefinitionData['Footnote'][$name]['count']++;

            if (!isset($this->DefinitionData['Footnote'][$name]['number'])) {
                $this->DefinitionData['Footnote'][$name]['number'] = ++$this->footnoteCount; # » &
            }

            $Element = array(
                'name' => 'sup',
                'attributes' => array('id' => 'fnref' . $this->DefinitionData['Footnote'][$name]['count'] . ':' . $name),
                'element' => array(
                    'name' => 'a',
                    'attributes' => array('href' => '#fn:' . $name, 'class' => 'footnote-ref'),
                    'text' => $this->DefinitionData['Footnote'][$name]['number'],
                ),
            );

            return array(
                'extent' => strlen($matches[0]),
                'element' => $Element,
            );
        }
    }

    private $footnoteCount = 0;

    #
    # Link

    protected function inlineLink($Excerpt)
    {
        $Link = parent::inlineLink($Excerpt);

        $remainder = $Link !== null ? substr($Excerpt['text'], $Link['extent']) : '';

        if (preg_match('/^[ ]*{(' . $this->regexAttribute . '+)}/', $remainder, $matches)) {
            $Link['element']['attributes'] += $this->parseAttributeData($matches[1]);

            $Link['extent'] += strlen($matches[0]);
        }

        return $Link;
    }

    #
    # ~
    #

    private $currentAbreviation;
    private $currentMeaning;

    protected function insertAbreviation(array $Element)
    {
        if (isset($Element['text'])) {
            $Element['elements'] = self::pregReplaceElements(
                '/\b' . preg_quote($this->currentAbreviation, '/') . '\b/',
                array(
                    array(
                        'name' => 'abbr',
                        'attributes' => array(
                            'title' => $this->currentMeaning,
                        ),
                        'text' => $this->currentAbreviation,
                    )
                ),
                $Element['text']
            );

            unset($Element['text']);
        }

        return $Element;
    }

    protected function inlineText($text)
    {
        $Inline = parent::inlineText($text);

        if (isset($this->DefinitionData['Abbreviation'])) {
            foreach ($this->DefinitionData['Abbreviation'] as $abbreviation => $meaning) {
                $this->currentAbreviation = $abbreviation;
                $this->currentMeaning = $meaning;

                $Inline['element'] = $this->elementApplyRecursiveDepthFirst(
                    array($this, 'insertAbreviation'),
                    $Inline['element']
                );
            }
        }

        return $Inline;
    }

    #
    # Util Methods
    #

    protected function addDdElement(array $Line, array $Block)
    {
        $text = substr($Line['text'], 1);
        $text = trim($text);

        unset($Block['dd']);

        $Block['dd'] = array(
            'name' => 'dd',
            'handler' => array(
                'function' => 'lineElements',
                'argument' => $text,
                'destination' => 'elements'
            ),
        );

        if (isset($Block['interrupted'])) {
            $Block['dd']['handler']['function'] = 'textElements';

            unset($Block['interrupted']);
        }

        $Block['element']['elements'][] = &$Block['dd'];

        return $Block;
    }

    protected function buildFootnoteElement()
    {
        $Element = array(
            'name' => 'div',
            'attributes' => array('class' => 'footnotes'),
            'elements' => array(
                array('name' => 'hr'),
                array(
                    'name' => 'ol',
                    'elements' => array(),
                ),
            ),
        );

        uasort($this->DefinitionData['Footnote'], 'self::sortFootnotes');

        foreach ($this->DefinitionData['Footnote'] as $definitionId => $DefinitionData) {
            if (!isset($DefinitionData['number'])) {
                continue;
            }

            $text = $DefinitionData['text'];

            $textElements = parent::textElements($text);

            $numbers = range(1, $DefinitionData['count']);

            $backLinkElements = array();

            foreach ($numbers as $number) {
                $backLinkElements[] = array('text' => ' ');
                $backLinkElements[] = array(
                    'name' => 'a',
                    'attributes' => array(
                        'href' => "#fnref$number:$definitionId",
                        'rev' => 'footnote',
                        'class' => 'footnote-backref',
                    ),
                    'rawHtml' => '&#8617;',
                    'allowRawHtmlInSafeMode' => true,
                    'autobreak' => false,
                );
            }

            unset($backLinkElements[0]);

            $n = count($textElements) - 1;

            if ($textElements[$n]['name'] === 'p') {
                $backLinkElements = array_merge(
                    array(
                        array(
                            'rawHtml' => '&#160;',
                            'allowRawHtmlInSafeMode' => true,
                        ),
                    ),
                    $backLinkElements
                );

                unset($textElements[$n]['name']);

                $textElements[$n] = array(
                    'name' => 'p',
                    'elements' => array_merge(
                        array($textElements[$n]),
                        $backLinkElements
                    ),
                );
            } else {
                $textElements[] = array(
                    'name' => 'p',
                    'elements' => $backLinkElements
                );
            }

            $Element['elements'][1]['elements'][] = array(
                'name' => 'li',
                'attributes' => array('id' => 'fn:' . $definitionId),
                'elements' => array_merge(
                    $textElements
                ),
            );
        }

        return $Element;
    }

    # ~

    protected function parseAttributeData($attributeString)
    {
        $Data = array();

        $attributes = preg_split('/[ ]+/', $attributeString, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($attributes as $attribute) {
            if ($attribute[0] === '#') {
                $Data['id'] = substr($attribute, 1);
            } else # "."
            {
                $classes[] = substr($attribute, 1);
            }
        }

        if (isset($classes)) {
            $Data['class'] = implode(' ', $classes);
        }

        return $Data;
    }

    # ~

    protected function processTag($elementMarkup) # recursive
    {
        # http://stackoverflow.com/q/1148928/200145
        libxml_use_internal_errors(true);

        $DOMDocument = new DOMDocument;

        # http://stackoverflow.com/q/11309194/200145
        $elementMarkup = mb_convert_encoding($elementMarkup, 'HTML-ENTITIES', 'UTF-8');

        # http://stackoverflow.com/q/4879946/200145
        $DOMDocument->loadHTML($elementMarkup);
        $DOMDocument->removeChild($DOMDocument->doctype);
        $DOMDocument->replaceChild($DOMDocument->firstChild->firstChild->firstChild, $DOMDocument->firstChild);

        $elementText = '';

        if ($DOMDocument->documentElement->getAttribute('markdown') === '1') {
            foreach ($DOMDocument->documentElement->childNodes as $Node) {
                $elementText .= $DOMDocument->saveHTML($Node);
            }

            $DOMDocument->documentElement->removeAttribute('markdown');

            $elementText = "\n" . $this->text($elementText) . "\n";
        } else {
            foreach ($DOMDocument->documentElement->childNodes as $Node) {
                $nodeMarkup = $DOMDocument->saveHTML($Node);

                if ($Node instanceof DOMElement and !in_array($Node->nodeName, $this->textLevelElements)) {
                    $elementText .= $this->processTag($nodeMarkup);
                } else {
                    $elementText .= $nodeMarkup;
                }
            }
        }

        # because we don't want for markup to get encoded
        $DOMDocument->documentElement->nodeValue = 'placeholder\x1A';

        $markup = $DOMDocument->saveHTML($DOMDocument->documentElement);
        $markup = str_replace('placeholder\x1A', $elementText, $markup);

        return $markup;
    }

    # ~

    protected function sortFootnotes($A, $B) # callback
    {
        return $A['number'] - $B['number'];
    }

    #
    # Fields
    #

    protected $regexAttribute = '(?:[#.][-\w]+[ ]*)';
}
?>