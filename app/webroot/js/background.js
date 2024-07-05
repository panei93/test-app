browser.runtime.onMessage.addListener(async message => {
  console.log("background: onMessage", message);
});

function onMessage(message) {
  console.log("background: onMessage", message);
	return Promise.resolve("Dummy response to keep the console quiet");
  // 1: Causes the following to be logged in content:
  // "The message port closed before a response was received."
  return undefined;

  // 2: Causes this response to be logged in content, as expected.
  // return Promise.resolve("response from background");

  // 3: Causes this error to be logged in content, as expected.
  // return Promise.reject(new Error("Could not respond"));

  // 4: Causes nothing at all to be logged in content!
  // I guess it is waiting for the deprecated `sendResponse` parameter to be
  // called.
  // return true;
}
