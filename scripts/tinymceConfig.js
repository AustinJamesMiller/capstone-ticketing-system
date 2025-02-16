tinymce.init({
  selector: "#editor",
  plugins:
    "a11ychecker advcode advlist advtable anchor autocorrect autosave editimage image link linkchecker lists media mediaembed pageembed powerpaste searchreplace table template tinymcespellchecker typography visualblocks wordcount",
  toolbar:
    "undo redo | styles | bold italic underline strikethrough | align | table link image media pageembed | bullist numlist outdent indent | spellcheckdialog a11ycheck typography code",
  height: 540,
  a11ychecker_level: "aaa",
  typography_langs: ["en-US"],
  typography_default_lang: "en-US",
  advcode_inline: true,
  content_style: `
        body {
          font-family: 'Roboto', sans-serif;
          color: #222;
        }
        img {
          height: auto;
          margin: auto;
          padding: 10px;
          display: block;
        }
        img.medium {
          max-width: 25%;
        }
        a {
          color: #116B59;
        }
        .related-content {
          padding: 0 10px;
          margin: 0 0 15px 15px;
          background: #eee;
          width: 200px;
          float: right;
        }
      `,
});
    setup: (editor) => {
        editor.on('change', (e) => {
          $.ajax({
            type: 'POST',
            url: 'php/save.php',
            data: {
              editor: tinymce.get('editor').getContent()
            },
            success: function(data){
              $('#editor').val('');
              console.log(data)
            }
          })
        })
      },

