MultiAvatar
===========

A simple PHP script for a fancy show-off displaying new avatar (a.k.a. userpic)
each time a user views your post on a forum.

Selects a random image file from given folder (defaults to `avatars/`)
and returns it to the browser as is supplying appropriate HTTP headers.

Host it a hosting of your choice, supply it an `avatars/` folder, pack
it with your favorite avs -- and here you go!

NB. Please do not expect it to work on forums or BBS that require avatar to be
uploaded or that store images on server side. MultiAvatar's response is renewed
upon request to your hosting, caching its result makes no sense.

If you like the screipt and would like to fork it you're welcome to do so.
But then please DO NOT add any image files into repository.
GitHub provides a great service for your code, and not your personal image hosting.

