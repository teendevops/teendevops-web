# teendevops / frontend development
This branch contains different people's work on the frontend.

Simply create html files with sample content. For example, if you were designing the profile page, you could use a fake username like "foodisgood225" or something with fake content. Later, if/when the backend is connected to the frontend, the backend devs will make the username be the actual username. All you need to worry about is making the static html pages. ;)

**Some info for you:**
* Please make a `themes` folder in the `css` folder that contains different color schemes for the pages. This way, later, the backend developers can make it possible to change your theme. For example, `/css/themes/blue.css`, `/css/themes/dark.css`, `/css/themes/holo.css`, etc.
* The type of design is really up to you. Material design or Paper would work.
* In the section below that explains the pages that are needed to be designed, please note that the `Content:` is only suggested content. If you can think of any ideas or don't think something belongs, you can totally add/remove it. In fact, change is good. Please try to add or remove things to make it different. :)
* It would be really awesome if you didn't use any dependencies like Bootstrap (jQuery&Ajax is fine though). This one is optional, but still. It would be great.

**These are the pages that are needed:**
* index.html
  * This is the main page that is displayed when a user visits https://teendevops.net/
  * Content:
    * A welcome message
* login.html
  * This is the login page accessible at https://teendevops.net/login/
  * Content:
    * A login form that consists of a username and password, and a signin button
    * A sample error message (for example, "Username or password incorrect."). The backend devs will make this only show if there actually is an error later.
* profile.html
  * This is the profile page, for example https://teendevops.net/profile/arinerron/
  * Content:
    * The username of course :P
    * The profile description
    * An email address (later the backend devs will make it optional to display the email on the profile)
    * A website link (also the devs will add support for this)
    * Location
    * Rank (for example, "User" or "Moderator" or "Admin"). If you can think of a better way to do this than having to display the rank, feel free to change it. Maybe just adding an asterisk next to the username so you know they aren't just a normal user? We'll see. :)
    * A dropdown with some options (not in this order per se):
      * "Block User"
      * "Report User"
      * "Edit Profile"
      * "Contact"
    * Number of ctf tokens (you probably don't have to do this one actually)
    * Please leave some room for a list of projects. The list of projects will have a short 150 char description, a title, and an icon. The title will also be a link to the project. You can find out how to design this, I'm sure.
  * If you really want to go above and beyond, try making it so you can edit the fields that you normally would have to edit from the `settings` file. Maybe, you'd click a button in a dropdown on your profile page that says "Edit Profile", and you could edit directly on your profile. Then when you press enter, it would use Ajax to save it (the backend devs can set that part up if you are unable). The settings you would need to make are listed in the `settings.html` section under `Content:`. If you do this, it would be good if you could make two `profile.html` files:
    * One for if a user is visiting their own profile (the content would be editable on this one)
    * One for if a user is visiting someone else's profile (the content would not be editable)
* register.html
  * You know what this is. The current one is available at https://teendevops.net/register/
  * Content:
    * A basic registration form, including username, email address, password, confirm password, and a submit button.
    * A sample error message (for example, "Please fill out all of the fields."). Like the login one, the devs will make this only display if there is an error.
* settings.html
  * This page is **only** needed if the settings aren't editable from the profile page (see profile.html)
  * This one is the settings page, available at https://teendevops.net/settings/
  * Content:
    * A text area for the user description
    * A text field for the user location
    * A way to set the user icon
    * A multi-text field for settings languages the user knows. Oh, this one is hard to explain. Okay. Ever posted a question on stackoverflow? Well, you know how you can set tags for the question? That field that you use to set them. That's what I mean. As you type in it, it comes up with suggestions. You can either click on them, or you can type it out and press enter (or space). If you need a better description of what this field is or don't understand, please do contact Arinerron on Google Hangouts.
    * A submit button (if these settings aren't on the profile)
* chat.html
  * You ready? This is the hard one. Here's the challenge. The current one is available at https://teendevops.net/chat/, but this should not be used as an example. It is far to crude to 

