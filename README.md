<h3>Setup process</h3>

<p>Project is using PHP 8.1</p>

<strong>OPTIONAL</strong>

<p>You need to update composer with <code>composer self-update</code> if you have issues installing packages</p>
<p>If you don't have <code>symfony</code> command, you'll need to follow this <a href="https://symfony.com/download">guide</a> to add it to your CLI</p>

<strong>STEPS</strong>

<ul>
<ol>1. <code>composer install</code> and <code>npm install</code> to install all the packages</ol>
<ol>2. copy .env.example to .env just to have an .env file</ol>
<ol>3. run <code>php artisan key:generate</code></ol>
<ol>4. run <code>npm run build</code> to create all css and js files</ol>
<ol>5. run <code>php artisan server</code> and go to the link it gives you</ol>
</ul>
