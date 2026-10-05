# The basic program structure

With the first render done, we spent the last week on the overall structure of the game client. A clean structure early on saves us a lot of pain later, so we took the time to get the foundations right.

## Game loop as a DLL

The game logic lives in its own library that is loaded by the platform layer. This allows us to recompile and reload the game code while the client keeps running, which speeds up iteration enormously.

## OpenGL and DirectX

The renderer is abstracted so that we can support both OpenGL and DirectX. This keeps our options open across platforms and helps us find rendering bugs that only show up with one API.

## Memory pools

Instead of allocating memory all over the place, the client reserves its memory up front and hands it out through memory pools. This makes memory usage predictable, avoids fragmentation and makes allocations very fast.
