Dropplets
=========

Dropplets is a minimalist **Markdown** blogging platform focused on delivering just what you need in a blogging solution, but absolutely nothing you don't. When it comes to basic blogging, all you really want to do is write & publish which is where Dropplets excels. It's databaseless, so it installs on any server in just about 30 seconds. 

Current version: 1.6.8

## What's Markdown?
Markdown is a text formatting syntax inspired by plain text email. It's extremely simple, memorizable and visually lightweight so as not to hinder reading.

> The idea is that a Markdown-formatted document should be publishable as-is, as plain text, without looking like it's been marked up with tags or formatting instructions.

## Getting Started
- [Installation](#installation)
- [Writing Posts](#writing-posts)
- [Publishing Posts](#publishing-posts)
- [Editing Posts](#editing-posts)
- [Changing Settings](#changing-settings)
- [Changing Templates](#changing-templates)
- [Adding Authors](#adding-authors)
- [Updating Dropplets](#updating-dropplets)

### Installation
Dropplets is compatible with most server configurations and can be typically installed in under a minute using the few step-by-step instructions below:

1. Download the latest release or current master branch and then extract the downloaded zip file.
3. Upload the entire contents of the extracted zip file to your web server wherever you want Dropplets to be installed. 
4. Pull up your site in any modern web browser (e.g. if you uploaded Dropplets to **yoursite.com**, load **yoursite.com** in your browser to finish the installation), then create and confirm your password.

### Writing Posts
With Dropplets, you write your posts offline (using the text or Markdown editor of your choice) in Markdown format. Here's a handy [syntax guide](https://github.com/circa75/dropplets/wiki/Markdown-Syntax-Guide) if you need a little help with your Markdown skills. All posts for Dropplets **MUST** be composed using the following format:

    # Your Post Title
    - Post Author Name (e.g. "Dropplets")
    - Post Author Twitter Handle (e.g. "dropplets")
    - Publish Date in YYYY/MM/DD Format (e.g. "2017/04/28")
    - Post Categories (e.g. "Random Thoughts, Other Thoughts")
    - Post Status (e.g. "published" or "draft")

    Your post text starts here. 
    
All posts must also be saved with the **.md** file extension. For instance, if your post title is **My First Blog Post**, your post file should look like this:

    my-first-blog-post.md

Some templates include the ability to add a post image or thumbnail along with your post in which should match your post file name like this:

    my-first-blog-post.jpg

Images can be uploaded in the admin panel

Post file names are used to structure post permalinks on your blog. So, a post file saved as **my-first-blog-post.md** will result in **yoursite.com/my-first-blog-post** as the post permalink.

### Publishing Posts
After you've finished writing your post offline, you can then publish your post using the Dropplets toolbar:

1. Login to your Dropplets installation using the password you created during the installation.
2. Click the Dropplet in your toolbar to select, upload and publish your posts and post images.

### Editing Posts
Re-upload your edited post file using the steps above. Doing this will automatically overwrite the existing post and publish your new edits. To delete a post, change the **Post Status** at the top of your post file to **draft**.

### Changing Settings
To change your blog settings, click the gear icon in the Dropplets toolbar. This will load the settings panel where you will be able to change all of your blog settings including your password.

### Changing Templates
To change your blog template, click the star icon in the Dropplets toolbar. This will load the templates panel where you will be able to browse and change your blog template.

### Adding Authors
Author metadata is stored in `.yml` files. To add an author, create a file called `author_name.yml`, with the following contents:

    name: Author Name
    location: My Location

Save it in the `authors/` directory and make sure that the directory is writable.

### Uploading Authors

1. Login to your Dropplets installation using the password you created during the installation and setup process.
2. Click the Dropplet in your toolbar to select `Upload Blog Authors`.

The following variables are available in their respective templates:

1. In `templates/your_template/post.php` and `templates/your_template/posts.php` -> `$post_author`
2. In `templates/your_template/author.php` -> `$author`

### Updating Dropplets
To update Dropplets, open the admin panel and click `Update Dropplets`

## Helping Out With Development
Here are some ways that you can help out with development.

### Using Droplets

#### Bug Reports
The first way that you can help out with development is by using Dropplets and submitting any bug reports or errors that you find.

#### Marketing
The second way you can help is by posting tutorials and/or articles about Dropplets.

### Developing Dropplets

If you have any expirence with HTML, CSS, JAVASCRIPT, or PHP web development, you can help us out by:

#### Adding Functionality
Everyone loves new features, especially when they have the power to drastically speed up our workflows. If you have an idea, code it and submit a pull request. 
Or, you could help us out by reviewing and or fixing existing pull requests.

#### Template Development
We do not currently have a full template development guide, but the page is up and coming [here](#). If you want to add your own templates, just use one of the existing ones as a guide!

#### Plugin Development
Right now, there is only one Dropplets plugin available! We need you to help make some more. A guide on how to do so is coming soon.