function formhash(form, password, password2) {
   // Create a new element input, this will be out hashed password field.
   var p = document.createElement("input");
   var p2 = document.createElement("input");
   // Add the new element to our form.
   form.appendChild(p);
   p.name = "p";
   p.type = "hidden"
   p.value = hex_sha512(password.value);
   
   form.appendChild(p2);
   p2.name = "p2";
   p2.type = "hidden"
   p2.value = hex_sha512(password2.value);
   // Make sure the plaintext password doesn't get sent.
   password.value = "";
   
   password2.value = "";
   // Finally submit the form.
   form.submit();
}