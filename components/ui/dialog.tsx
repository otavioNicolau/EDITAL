"use client"

import * as React from "react"
import { X } from "lucide-react"
import { cn } from "@/lib/utils"

interface DialogContextType {
  open: boolean
  onOpenChange: (open: boolean) => void
}

const DialogContext = React.createContext<DialogContextType | undefined>(undefined)

interface DialogProps {
  children: React.ReactNode
  open?: boolean
  onOpenChange?: (open: boolean) => void
}

const Dialog: React.FC<DialogProps> = ({ children, open = false, onOpenChange }) => {
  const [isOpen, setIsOpen] = React.useState(open)
  
  React.useEffect(() => {
    setIsOpen(open)
  }, [open])
  
  const handleOpenChange = (newOpen: boolean) => {
    setIsOpen(newOpen)
    onOpenChange?.(newOpen)
  }
  
  return (
    <DialogContext.Provider value={{ open: isOpen, onOpenChange: handleOpenChange }}>
      {children}
    </DialogContext.Provider>
  )
}

interface DialogTriggerProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  asChild?: boolean
}

const DialogTrigger = React.forwardRef<
  HTMLButtonElement,
  DialogTriggerProps
>(({ className, children, asChild = false, ...props }, ref) => {
  const context = React.useContext(DialogContext)
  
  const handleClick = () => context?.onOpenChange(true)
  
  if (asChild) {
    return React.cloneElement(
      children as React.ReactElement,
      {
        onClick: handleClick,
        ...props
      }
    )
  }
  
  return (
    <button
      ref={ref}
      className={className}
      onClick={handleClick}
      {...props}
    >
      {children}
    </button>
  )
})
DialogTrigger.displayName = "DialogTrigger"

interface DialogContentProps extends React.HTMLAttributes<HTMLDivElement> {
  children: React.ReactNode
}

const DialogContent = React.forwardRef<HTMLDivElement, DialogContentProps>(
  ({ className, children, ...props }, ref) => {
    const context = React.useContext(DialogContext)
    
    if (!context?.open) return null
    
    return (
      <>
        <div 
          className="modal-backdrop fade show" 
          onClick={() => context.onOpenChange(false)}
        />
        <div className="modal fade show d-block" tabIndex={-1}>
          <div className={cn("modal-dialog modal-dialog-centered", className)} ref={ref} {...props}>
            <div className="modal-content">
              {children}
              <button
                type="button"
                className="btn-close position-absolute top-0 end-0 m-3"
                onClick={() => context.onOpenChange(false)}
              >
                <X size={16} />
                <span className="visually-hidden">Close</span>
              </button>
            </div>
          </div>
        </div>
      </>
    )
  }
)
DialogContent.displayName = "DialogContent"

const DialogHeader = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div
      ref={ref}
      className={cn("modal-header d-flex flex-column", className)}
      {...props}
    />
  )
)
DialogHeader.displayName = "DialogHeader"

const DialogFooter = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div
      ref={ref}
      className={cn("modal-footer d-flex justify-content-end gap-2", className)}
      {...props}
    />
  )
)
DialogFooter.displayName = "DialogFooter"

const DialogTitle = React.forwardRef<HTMLHeadingElement, React.HTMLAttributes<HTMLHeadingElement>>(
  ({ className, ...props }, ref) => (
    <h5
      ref={ref}
      className={cn("modal-title fw-semibold", className)}
      {...props}
    />
  )
)
DialogTitle.displayName = "DialogTitle"

const DialogDescription = React.forwardRef<HTMLParagraphElement, React.HTMLAttributes<HTMLParagraphElement>>(
  ({ className, ...props }, ref) => (
    <p
      ref={ref}
      className={cn("text-muted small", className)}
      {...props}
    />
  )
)
DialogDescription.displayName = "DialogDescription"

export {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogFooter,
  DialogTitle,
  DialogDescription,
}
