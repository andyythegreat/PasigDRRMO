export function convertHtmlToPlainText(html) {
    // Replace HTML tags with an empty string to strip them out
    const plainText = html.replace(/<\/?[^>]+(>|$)/g, '');
  
    // Return the cleaned plain text
    return plainText.trim();
  }
  