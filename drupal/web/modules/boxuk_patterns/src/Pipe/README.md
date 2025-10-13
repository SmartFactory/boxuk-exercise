# Pipeline Framework

## ⚙️ Framework Code - Don't modify!

This directory contains the **pipeline framework** - the underlying system that makes the pipe pattern work.

**Developers don't need to modify or understand this code.** It's a "black box" that just works.

## What's in Here?

- **Contract/** - Interfaces that define the pipeline API
  - `PipeContract.php` - Contract for individual pipes
  - `PipelineContract.php` - Contract for the pipeline orchestrator

- **BasePipe.php** - Abstract base class that all pipes extend
- **BasePipeline.php** - Abstract base class for pipeline implementations
- **Pipeline.php** - Concrete pipeline implementation

## For Developers: Where to Work

**👉 Your pipes go in `src/StyleData/Pipe/`**

That's the application code directory where you create your custom pipes.

## How It Works (High Level)

```
Your Pipe → extends BasePipe → implements PipeContract
                                       ↓
                            Pipeline processes all pipes
                                       ↓
                            Merges all results into one array
                                       ↓
                            Returns to template
```

You only need to:
1. Create a class in `src/StyleData/Pipe/`
2. Extend `BasePipe`
3. Implement `handle(): array`

The framework handles the rest!

## When Would You Modify This?

You probably won't need to, but you might if:

- You want to change how pipes are validated
- You need custom array merging logic
- You want to add pipeline middleware
- You're extending the framework for other entity types

For 99% of use cases, you don't touch this directory.

## Architecture

If you're curious about the internals, see `/ARCHITECTURE.md` for detailed diagrams and explanations.

## Questions?

- **To create pipes**: See `src/StyleData/Pipe/README.md`
- **For examples**: See `/EXAMPLES.md`
- **Quick reference**: See `/QUICK_START.md`
- **Full docs**: See `/README.md`
