"use client"

import * as React from "react"
import { cn } from "@/lib/utils"

interface AlertDialogContextType {
  open: boolean
  onOpenChange: (open: boolean) => void
}

const AlertDialogContext = React.createContext<AlertDialogContextType | undefined>(undefined)

interface AlertDialogProps {
  children: React.ReactNode
  open?: boolean
  onOpenChange?: (open: boolean) => void
}

const AlertDialog: React.FC<AlertDialogProps> = ({ children, open = false, onOpenChange }) => {
  const [isOpen, setIsOpen] = React.useState(open)
  
  React.useEffect(() => {
    setIsOpen(open)
  }, [open])
  
  const handleOpenChange = (newOpen: boolean) => {
    setIsOpen(newOpen)
    onOpenChange?.(newOpen)
  }
  
  return (
    <AlertDialogContext.Provider value={{ open: isOpen, onOpenChange: handleOpenChange }}>
      {children}
    </AlertDialogContext.Provider>
  )
}

interface AlertDialogTriggerProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  asChild?: boolean
}

const AlertDialogTrigger = React.forwardRef<
  HTMLButtonElement,
  AlertDialogTriggerProps
>(({ className, children, asChild = false, ...props }, ref) => {
  const context = React.useContext(AlertDialogContext)
  
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
AlertDialogTrigger.displayName = "AlertDialogTrigger"

interface AlertDialogContentProps extends React.HTMLAttributes<HTMLDivElement> {
  children: React.ReactNode
}

const AlertDialogContent = React.forwardRef<HTMLDivElement, AlertDialogContentProps>(
  ({ className, children, ...props }, ref) => {
    const context = React.useContext(AlertDialogContext)
    
    if (!context?.open) return null
    
    return (
      <>
        <div className="modal-backdrop fade show" />
        <div className="modal fade show d-block" tabIndex={-1}>
          <div className={cn("modal-dialog modal-dialog-centered", className)} ref={ref} {...props}>
            <div className="modal-content">
              {children}
            </div>
          </div>
        </div>
      </>
    )
  }
)
AlertDialogContent.displayName = "AlertDialogContent"

const AlertDialogHeader = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div
      ref={ref}
      className={cn("modal-header d-flex flex-column", className)}
      {...props}
    />
  )
)
AlertDialogHeader.displayName = "AlertDialogHeader"

const AlertDialogFooter = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div
      ref={ref}
      className={cn("modal-footer d-flex justify-content-end gap-2", className)}
      {...props}
    />
  )
)
AlertDialogFooter.displayName = "AlertDialogFooter"

const AlertDialogTitle = React.forwardRef<HTMLHeadingElement, React.HTMLAttributes<HTMLHeadingElement>>(
  ({ className, ...props }, ref) => (
    <h5
      ref={ref}
      className={cn("modal-title fw-semibold", className)}
      {...props}
    />
  )
)
AlertDialogTitle.displayName = "AlertDialogTitle"

const AlertDialogDescription = React.forwardRef<HTMLParagraphElement, React.HTMLAttributes<HTMLParagraphElement>>(
  ({ className, ...props }, ref) => (
    <p
      ref={ref}
      className={cn("text-muted small", className)}
      {...props}
    />
  )
)
AlertDialogDescription.displayName = "AlertDialogDescription"

const AlertDialogAction = React.forwardRef<HTMLButtonElement, React.ButtonHTMLAttributes<HTMLButtonElement>>(
  ({ className, ...props }, ref) => {
    const context = React.useContext(AlertDialogContext)
    
    return (
      <button
        ref={ref}
        className={cn("btn btn-primary", className)}
        onClick={(e) => {
          props.onClick?.(e)
          context?.onOpenChange(false)
        }}
        {...props}
      />
    )
  }
)
AlertDialogAction.displayName = "AlertDialogAction"

const AlertDialogCancel = React.forwardRef<HTMLButtonElement, React.ButtonHTMLAttributes<HTMLButtonElement>>(
  ({ className, ...props }, ref) => {
    const context = React.useContext(AlertDialogContext)
    
    return (
      <button
        ref={ref}
        className={cn("btn btn-outline-secondary", className)}
        onClick={(e) => {
          props.onClick?.(e)
          context?.onOpenChange(false)
        }}
        {...props}
      />
    )
  }
)
AlertDialogCancel.displayName = "AlertDialogCancel"

export {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogFooter,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogAction,
  AlertDialogCancel,
}
